<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use App\Mail\VerifyEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;

class TeamAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:team-admin');
    }

    /**
     * Display a listing of team members
     */
    public function index(Request $request)
    {
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
        $roles = Role::all();
        return view('team-admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created team member
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'team_id' => Auth::user()->team_id, // Automatically assign to current team
            'is_system_user' => false, // Always team user
        ]);

        $role = Role::find($validated['role_id']);
        $user->assignRole($role);

        // Send verification email
        try {
            $token = Str::random(64);
            $user->update([
                'email_verification_token' => $token,
                'email_verification_expires_at' => now()->addHours(1),
            ]);

            $verificationUrl = URL::temporarySignedRoute(
                'verification.verify',
                now()->addHour(),
                ['id' => $user->id, 'token' => $token]
            );

            Mail::to($user->email)->send(new VerifyEmail($user, $verificationUrl));
        } catch (\Exception $e) {
            // Log error but don't fail the user creation
            Log::error('Failed to send verification email: ' . $e->getMessage());
        }

        return redirect()->route('team-admin.users.index')->with('success', 'Thành viên đã được thêm vào team thành công. Email xác thực đã được gửi.');
    }

    /**
     * Display the specified team member
     */
    public function show(User $user)
    {
        // Ensure user belongs to the same team
        if ($user->team_id !== Auth::user()->team_id) {
            abort(403, 'Bạn không có quyền xem thành viên này.');
        }

        $user->load(['roles.permissions', 'team']);
        return view('team-admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified team member
     */
    public function edit(User $user)
    {
        // Ensure user belongs to the same team
        if ($user->team_id !== Auth::user()->team_id) {
            abort(403, 'Bạn không có quyền chỉnh sửa thành viên này.');
        }

        $roles = Role::all();
        return view('team-admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified team member
     */
    public function update(Request $request, User $user)
    {
        // Ensure user belongs to the same team
        if ($user->team_id !== Auth::user()->team_id) {
            abort(403, 'Bạn không có quyền chỉnh sửa thành viên này.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
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

        // Update role
        $role = Role::find($validated['role_id']);
        $user->syncRoles([$role]);

        return redirect()->route('team-admin.users.index')->with('success', 'Thông tin thành viên đã được cập nhật thành công.');
    }

    /**
     * Remove the specified team member from team
     */
    public function destroy(User $user)
    {
        // Ensure user belongs to the same team
        if ($user->team_id !== Auth::user()->team_id) {
            abort(403, 'Bạn không có quyền xóa thành viên này.');
        }

        // Don't allow team admin to remove themselves
        if ($user->id === Auth::id()) {
            return redirect()->route('team-admin.users.index')->with('error', 'Bạn không thể xóa chính mình khỏi team.');
        }

        // Remove from team (set team_id to null) instead of deleting
        $user->update(['team_id' => null]);

        return redirect()->route('team-admin.users.index')->with('success', 'Thành viên đã được xóa khỏi team thành công.');
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
}
