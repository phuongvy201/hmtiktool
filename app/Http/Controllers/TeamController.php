<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use App\Helpers\TeamPermissionHelper;
use App\Models\TeamTikTokMarket;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('view-teams');

        // Team-admin is not allowed to manage/view team listing
        if (TeamPermissionHelper::isTeamAdmin()) {
            abort(403, 'Team admin is not allowed to manage teams.');
        }

        $query = Team::with(['users']);

        // Apply team filtering for team-admin
        $query = TeamPermissionHelper::filterTeams($query);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by member count
        if ($request->filled('member_count')) {
            $memberCount = $request->member_count;
            if ($memberCount === '0') {
                $query->whereDoesntHave('users');
            } elseif ($memberCount === '1-5') {
                $query->whereHas('users', function ($q) {
                    $q->havingRaw('COUNT(*) BETWEEN 1 AND 5');
                });
            } elseif ($memberCount === '6-10') {
                $query->whereHas('users', function ($q) {
                    $q->havingRaw('COUNT(*) BETWEEN 6 AND 10');
                });
            } elseif ($memberCount === '10+') {
                $query->whereHas('users', function ($q) {
                    $q->havingRaw('COUNT(*) > 10');
                });
            }
        }

        $teams = $query->paginate(10)->withQueryString();
        return view('teams.index', compact('teams'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create-teams');

        // Team admin cannot create new teams
        if (TeamPermissionHelper::isTeamAdmin()) {
            abort(403, 'Team Admin không thể tạo team mới.');
        }

        $users = TeamPermissionHelper::getTeamLevelUsers();
        return view('teams.create', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create-teams');

        // Team admin cannot create new teams
        if (TeamPermissionHelper::isTeamAdmin()) {
            abort(403, 'Team Admin không thể tạo team mới.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:teams',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive,suspended',
            'members' => 'nullable|array',
            'members.*' => 'exists:users,id',
            'markets' => 'nullable|array',
            'markets.*' => 'in:US,UK',
        ]);

        $team = Team::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'status' => $validated['status'],
        ]);

        // Assign members to team
        if (!empty($validated['members'])) {
            User::whereIn('id', $validated['members'])->update(['team_id' => $team->id]);
        }

        // Assign TikTok markets
        if (!empty($validated['markets'])) {
            $payload = collect($validated['markets'])->unique()->map(function ($market) use ($team) {
                return [
                    'team_id' => $team->id,
                    'market' => $market,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->toArray();
            TeamTikTokMarket::insert($payload);
        }

        return redirect()->route('teams.index')->with('success', 'Team đã được tạo thành công.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Team $team)
    {
        $this->authorize('view-teams');

        // Check if team-admin can access this team
        if (!TeamPermissionHelper::canManageTeam($team)) {
            abort(403, 'Bạn không có quyền xem team này.');
        }

        $team->load(['users.roles', 'users.team']);
        return view('teams.show', compact('team'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Team $team)
    {
        $this->authorize('edit-teams');

        // Check if team-admin can access this team
        if (!TeamPermissionHelper::canManageTeam($team)) {
            abort(403, 'Bạn không có quyền chỉnh sửa team này.');
        }

        $users = TeamPermissionHelper::getTeamLevelUsers();
        return view('teams.edit', compact('team', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Team $team)
    {
        $this->authorize('edit-teams');

        // Check if team-admin can access this team
        if (!TeamPermissionHelper::canManageTeam($team)) {
            abort(403, 'Bạn không có quyền chỉnh sửa team này.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:teams,name,' . $team->id,
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive,suspended',
            'members' => 'nullable|array',
            'members.*' => 'exists:users,id',
            'markets' => 'nullable|array',
            'markets.*' => 'in:US,UK',
        ]);

        $team->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'status' => $validated['status'],
        ]);

        // Update team members
        if (isset($validated['members'])) {
            // Remove current members from this team
            User::where('team_id', $team->id)->update(['team_id' => null]);

            // Assign new members
            if (!empty($validated['members'])) {
                User::whereIn('id', $validated['members'])->update(['team_id' => $team->id]);
            }
        }

        // Update TikTok markets
        $team->tiktokMarkets()->delete();
        if (!empty($validated['markets'])) {
            $payload = collect($validated['markets'])->unique()->map(function ($market) use ($team) {
                return [
                    'team_id' => $team->id,
                    'market' => $market,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->toArray();
            TeamTikTokMarket::insert($payload);
        }

        return redirect()->route('teams.index')->with('success', 'Team đã được cập nhật thành công.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Team $team)
    {
        $this->authorize('delete-teams');

        // Check if team-admin can access this team
        if (!TeamPermissionHelper::canManageTeam($team)) {
            abort(403, 'Bạn không có quyền xóa team này.');
        }

        // Check if team has members
        if ($team->users()->count() > 0) {
            return redirect()->route('teams.index')->with('error', 'Không thể xóa team đang có thành viên.');
        }

        $team->delete();

        return redirect()->route('teams.index')->with('success', 'Team đã được xóa thành công.');
    }
}
