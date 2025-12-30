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

class UserController extends Controller
{
    private EmailVerificationService $emailVerificationService;

    public function __construct(EmailVerificationService $emailVerificationService)
    {
        $this->emailVerificationService = $emailVerificationService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('view-users');

            // Seller is not authorized to view the list of team members
        $user = Auth::user();
        if ($user && $user->hasRole('seller')) {
            abort(403, 'You are not authorized to view the list of team members.');
        }

        $query = User::with(['roles', 'team']);

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

        // Filter by user type
        if ($request->filled('type')) {
            if ($request->type === 'system') {
                $query->where('is_system_user', true);
            } elseif ($request->type === 'team') {
                $query->where('is_system_user', false);
            }
        }

        $users = $query->paginate(10)->withQueryString();
        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create-users');

        $teams = Team::all();
        $roles = Role::all();

        return view('users.create', compact('teams', 'roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create-users');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'team_id' => 'nullable|exists:teams,id',
            'is_system_user' => 'boolean',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'team_id' => $validated['team_id'],
            'is_system_user' => $validated['is_system_user'] ?? false,
        ]);

        $role = Role::find($validated['role_id']);
        $user->assignRole($role);

        // Send verification email
        try {
            $sent = $this->emailVerificationService->sendVerificationEmail($user);
            if ($sent) {
                return redirect()->route('users.index')->with('success', 'User created successfully. Verification email sent.');
            }
        } catch (\Exception $e) {
            Log::error('Failed to send verification email when creating user', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }

        return redirect()->route('users.index')->with('success', 'User created successfully. Verification email not sent.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $this->authorize('view-users');

        $user->load(['roles.permissions', 'team']);
        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $this->authorize('edit-users');

        $teams = Team::all();
        $roles = Role::all();

        return view('users.edit', compact('user', 'teams', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $this->authorize('edit-users');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'team_id' => 'nullable|exists:teams,id',
            'is_system_user' => 'boolean',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'team_id' => $validated['team_id'],
            'is_system_user' => $validated['is_system_user'] ?? false,
        ]);

        if ($validated['password']) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        // Update role
        $role = Role::find($validated['role_id']);
        $user->syncRoles([$role]);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}
