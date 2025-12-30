<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Team;
use App\Services\EmailVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\UserTikTokMarket;

class TeamAdminController extends Controller
{
    private EmailVerificationService $emailVerificationService;

    public function __construct(EmailVerificationService $emailVerificationService)
    {
        $this->middleware('auth');
        $this->middleware('role:team-admin');
        $this->emailVerificationService = $emailVerificationService;
    }

    /**
     * Display a listing of team members
     */
    public function index(Request $request)
    {
        $this->authorize('view-users');

        $query = User::with(['roles', 'team'])
            ->where('team_id', Auth::user()->team_id)
            ->where('is_system_user', false);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->whereNotNull('email_verified_at');
            } elseif ($request->status === 'inactive') {
                $query->whereNull('email_verified_at');
            }
        }

        $users = $query->paginate(10)->withQueryString();
        $roles = Role::all();

        return view('team-admin.users', compact('users', 'roles'));
    }

    /**
     * Show the form for creating a new team member
     */
    public function create()
    {
        $this->authorize('create-users');

        // Chỉ lấy roles cấp team, không lấy roles cấp hệ thống (system- roles)
        $roles = $this->getTeamLevelRoles();
        return view('team-admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created team member
     */
    public function store(Request $request)
    {
        $this->authorize('create-users');

        // Get list of team role IDs to validate
        $teamLevelRoleIds = $this->getTeamLevelRoles()->pluck('id')->toArray();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => ['required', 'exists:roles,id', Rule::in($teamLevelRoleIds)],
            'market' => 'nullable|string|in:US,UK',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'team_id' => Auth::user()->team_id, // Automatically assign to current team
            'is_system_user' => false, // Always team user
        ]);

        // Save TikTok market (single selection)
        $user->tiktokMarkets()->delete();
        if (!empty($validated['market'])) {
            UserTikTokMarket::create([
                'user_id' => $user->id,
                'market' => $validated['market'],
            ]);
        }

        $role = Role::find($validated['role_id']);
        $user->assignRole($role);

        // Send verification email to new user
        try {
            $sent = $this->emailVerificationService->sendVerificationEmail($user);
            if ($sent) {
                return redirect()->route('team-admin.users.index')->with('success', 'Member added to team successfully. Verification email has been sent.');
            }
        } catch (\Exception $e) {
            // Log error but don't fail the user creation
            Log::error('Failed to send verification email: ' . $e->getMessage());
        }

        return redirect()->route('team-admin.users.index')
            ->with('success', 'Member added to team successfully.')
            ->with('error', 'Verification email not sent, please send again from profile page.');
    }

    /**
     * Display the specified team member
     */
    public function show(User $user)
    {
        $this->authorize('view-users');

        // Ensure user belongs to the same team
        if ($user->team_id !== Auth::user()->team_id) {
            abort(403, 'You do not have permission to view this member.');
        }

        $user->load(['roles.permissions', 'team']);
        return view('team-admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified team member
     */
    public function edit(User $user)
    {
        $this->authorize('edit-users');

        // Ensure user belongs to the same team
        if ($user->team_id !== Auth::user()->team_id) {
            abort(403, 'You do not have permission to edit this member.');
        }

        // Chỉ lấy roles cấp team, không lấy roles cấp hệ thống (system- roles)
        $roles = $this->getTeamLevelRoles();

        return view('team-admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified team member
     */
    public function update(Request $request, User $user)
    {
        $this->authorize('edit-users');

        // Ensure user belongs to the same team
        if ($user->team_id !== Auth::user()->team_id) {
            abort(403, 'Bạn không có quyền chỉnh sửa thành viên này.');
        }

        // Get list of team role IDs to validate
        $teamLevelRoleIds = $this->getTeamLevelRoles()->pluck('id')->toArray();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role_id' => ['required', 'exists:roles,id', Rule::in($teamLevelRoleIds)],
            'market' => 'nullable|string|in:US,UK',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'team_id' => Auth::user()->team_id, // Keep in same team
            'is_system_user' => false, // Always team user
        ]);

        if ($validated['password']) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        // Update TikTok market (single selection)
        $user->tiktokMarkets()->delete();
        if (!empty($validated['market'])) {
            UserTikTokMarket::create([
                'user_id' => $user->id,
                'market' => $validated['market'],
            ]);
        }

        // Update role
        $role = Role::find($validated['role_id']);
        $user->syncRoles([$role]);

        return redirect()->route('team-admin.users.index')->with('success', 'Member information updated successfully.');
    }

    /**
     * Remove the specified team member from team
     */
    public function destroy(User $user)
    {
        $this->authorize('edit-users');

        // Ensure user belongs to the same team
        if ($user->team_id !== Auth::user()->team_id) {
            abort(403, 'You do not have permission to delete this member.');
        }

        // Don't allow team admin to remove themselves
        if ($user->id === Auth::id()) {
            return redirect()->route('team-admin.users.index')->with('error', 'You cannot delete yourself from the team.');
        }

        // Remove from team (set team_id to null) instead of deleting
        $user->update(['team_id' => null]);

        return redirect()->route('team-admin.users.index')->with('success', 'Member deleted from team successfully.');
    }

    /**
     * Show team dashboard
     */
    public function dashboard()
    {
        $team = Auth::user()->team;
        $teamMembers = User::where('team_id', Auth::user()->team_id)
            ->where('is_system_user', false)
            ->with('roles')
            ->get();

        $stats = [
            'total_members' => $teamMembers->count(),
            'verified_members' => $teamMembers->where('email_verified_at', '!=', null)->count(),
            'different_roles' => $teamMembers->pluck('roles')->flatten()->unique('id')->count(),
        ];

        return view('team-admin.dashboard', compact('team', 'teamMembers', 'stats'));
    }

    /**
     * Display team roles and permissions
     */
    public function teamRoles()
    {
        $team = Auth::user()->team;
        $teamMembers = User::with(['roles'])
            ->where('team_id', $team->id)
            ->where('is_system_user', false)
            ->get();

        // Get unique roles used by team members
        $teamRoles = collect();
        foreach ($teamMembers as $member) {
            $teamRoles = $teamRoles->merge($member->roles);
        }
        $teamRoles = $teamRoles->unique('id');

        return view('team-admin.roles', compact('team', 'teamRoles', 'teamMembers'));
    }

    /**
     * Get list of team roles (excluding system- roles)
     * System- roles: all roles with name starting with "system-"
     */
    private function getTeamLevelRoles()
    {
        // Exclude all roles with name starting with "system-"
        // Team roles are usually: team-admin, team-member, or custom roles
        return Role::where('name', 'not like', 'system-%')
            ->where('name', '!=', 'super-admin')
            ->get();
    }
}
