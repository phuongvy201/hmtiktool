<?php

namespace App\Http\Controllers;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('view-roles');

        $query = Role::with(['permissions', 'users']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        // Filter by permission count
        if ($request->filled('permission_count')) {
            $permissionCount = $request->permission_count;
            if ($permissionCount === '0') {
                $query->whereDoesntHave('permissions');
            } elseif ($permissionCount === '1-5') {
                $query->whereHas('permissions', function ($q) {
                    $q->havingRaw('COUNT(*) BETWEEN 1 AND 5');
                });
            } elseif ($permissionCount === '6-10') {
                $query->whereHas('permissions', function ($q) {
                    $q->havingRaw('COUNT(*) BETWEEN 6 AND 10');
                });
            } elseif ($permissionCount === '10+') {
                $query->whereHas('permissions', function ($q) {
                    $q->havingRaw('COUNT(*) > 10');
                });
            }
        }

        // Filter by user count
        if ($request->filled('user_count')) {
            $userCount = $request->user_count;
            if ($userCount === '0') {
                $query->whereDoesntHave('users');
            } elseif ($userCount === '1-5') {
                $query->whereHas('users', function ($q) {
                    $q->havingRaw('COUNT(*) BETWEEN 1 AND 5');
                });
            } elseif ($userCount === '6-10') {
                $query->whereHas('users', function ($q) {
                    $q->havingRaw('COUNT(*) BETWEEN 6 AND 10');
                });
            } elseif ($userCount === '10+') {
                $query->whereHas('users', function ($q) {
                    $q->havingRaw('COUNT(*) > 10');
                });
            }
        }

        $roles = $query->paginate(10)->withQueryString();
        return view('roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create-roles');

        // Only team admin (not system admin) cannot create roles
        if (Auth::user()->hasRole('team-admin') && !Auth::user()->hasRole('system-admin')) {
            abort(403, 'Team Admin không thể tạo vai trò mới.');
        }

        $permissions = Permission::all();
        return view('roles.create', compact('permissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create-roles');

        // Only team admin (not system admin) cannot create roles
        if (Auth::user()->hasRole('team-admin') && !Auth::user()->hasRole('system-admin')) {
            abort(403, 'Team Admin không thể tạo vai trò mới.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role = Role::create(['name' => $validated['name']]);

        if (isset($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return redirect()->route('roles.index')->with('success', 'Vai trò đã được tạo thành công.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        $this->authorize('view-roles');

        $role->load(['permissions', 'users.team']);
        return view('roles.show', compact('role'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        $this->authorize('edit-roles');

        // Only team admin (not system admin) cannot edit roles
        if (Auth::user()->hasRole('team-admin') && !Auth::user()->hasRole('system-admin')) {
            abort(403, 'Team Admin không thể chỉnh sửa vai trò.');
        }

        $permissions = Permission::all();
        return view('roles.edit', compact('role', 'permissions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        $this->authorize('edit-roles');

        // Only team admin (not system admin) cannot edit roles
        if (Auth::user()->hasRole('team-admin') && !Auth::user()->hasRole('system-admin')) {
            abort(403, 'Team Admin không thể chỉnh sửa vai trò.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role->update(['name' => $validated['name']]);

        if (isset($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return redirect()->route('roles.index')->with('success', 'Vai trò đã được cập nhật thành công.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        $this->authorize('delete-roles');

        // Only team admin (not system admin) cannot delete roles
        if (Auth::user()->hasRole('team-admin') && !Auth::user()->hasRole('system-admin')) {
            abort(403, 'Team Admin không thể xóa vai trò.');
        }

        // Check if role has users
        if ($role->users()->count() > 0) {
            return redirect()->route('roles.index')->with('error', 'Không thể xóa vai trò đang có người dùng.');
        }

        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Vai trò đã được xóa thành công.');
    }
}
