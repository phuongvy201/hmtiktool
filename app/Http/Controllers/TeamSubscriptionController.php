<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\TeamSubscription;
use App\Models\ServicePackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class TeamSubscriptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = TeamSubscription::with(['team', 'servicePackage', 'assignedBy']);

        // Filter by team
        if ($request->filled('team_id')) {
            $query->where('team_id', $request->team_id);
        }

        // Filter by service package
        if ($request->filled('service_package_id')) {
            $query->where('service_package_id', $request->service_package_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->where('start_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('end_date', '<=', $request->end_date);
        }

        $subscriptions = $query->orderBy('created_at', 'desc')->paginate(15);
        $teams = Team::orderBy('name')->get();
        $packages = ServicePackage::active()->orderBy('name')->get();

        return view('team-subscriptions.index', compact('subscriptions', 'teams', 'packages'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $teams = Team::orderBy('name')->get();
        $packages = ServicePackage::active()->orderBy('name')->get();

        return view('team-subscriptions.create', compact('teams', 'packages'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'team_id' => 'required|exists:teams,id',
            'service_package_id' => 'required|exists:service_packages,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status' => 'required|in:active,expired,cancelled,pending',
            'paid_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string|max:255',
            'transaction_id' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'auto_renew' => 'boolean',
        ]);

        try {
            DB::beginTransaction();

            // Check if team already has an active subscription for the same package
            $existingSubscription = TeamSubscription::where('team_id', $request->team_id)
                ->where('service_package_id', $request->service_package_id)
                ->where('status', 'active')
                ->where('end_date', '>=', now())
                ->first();

            if ($existingSubscription) {
                return back()->withErrors(['error' => 'Team đã có gói dịch vụ này đang hoạt động.']);
            }

            $subscription = TeamSubscription::create([
                'team_id' => $request->team_id,
                'service_package_id' => $request->service_package_id,
                'assigned_by' => Auth::id(),
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'status' => $request->status,
                'paid_amount' => $request->paid_amount,
                'payment_method' => $request->payment_method,
                'transaction_id' => $request->transaction_id,
                'notes' => $request->notes,
                'auto_renew' => $request->boolean('auto_renew'),
            ]);

            DB::commit();

            return redirect()->route('team-subscriptions.index')
                ->with('success', 'Gói dịch vụ đã được gán thành công cho team.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(TeamSubscription $teamSubscription)
    {
        $teamSubscription->load(['team', 'servicePackage', 'assignedBy']);

        return view('team-subscriptions.show', compact('teamSubscription'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TeamSubscription $teamSubscription)
    {
        $teams = Team::orderBy('name')->get();
        $packages = ServicePackage::active()->orderBy('name')->get();

        return view('team-subscriptions.edit', compact('teamSubscription', 'teams', 'packages'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TeamSubscription $teamSubscription)
    {
        $request->validate([
            'team_id' => 'required|exists:teams,id',
            'service_package_id' => 'required|exists:service_packages,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status' => 'required|in:active,expired,cancelled,pending',
            'paid_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string|max:255',
            'transaction_id' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'auto_renew' => 'boolean',
        ]);

        try {
            DB::beginTransaction();

            // Check if team already has an active subscription for the same package (excluding current)
            $existingSubscription = TeamSubscription::where('team_id', $request->team_id)
                ->where('service_package_id', $request->service_package_id)
                ->where('status', 'active')
                ->where('end_date', '>=', now())
                ->where('id', '!=', $teamSubscription->id)
                ->first();

            if ($existingSubscription) {
                return back()->withErrors(['error' => 'Team đã có gói dịch vụ này đang hoạt động.']);
            }

            $teamSubscription->update([
                'team_id' => $request->team_id,
                'service_package_id' => $request->service_package_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'status' => $request->status,
                'paid_amount' => $request->paid_amount,
                'payment_method' => $request->payment_method,
                'transaction_id' => $request->transaction_id,
                'notes' => $request->notes,
                'auto_renew' => $request->boolean('auto_renew'),
            ]);

            DB::commit();

            return redirect()->route('team-subscriptions.index')
                ->with('success', 'Thông tin gói dịch vụ đã được cập nhật thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TeamSubscription $teamSubscription)
    {
        try {
            $teamSubscription->delete();
            return redirect()->route('team-subscriptions.index')
                ->with('success', 'Gói dịch vụ đã được xóa thành công.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }

    /**
     * Assign package to team from team management page
     */
    public function assignToTeam(Request $request, Team $team)
    {
        $request->validate([
            'service_package_id' => 'required|exists:service_packages,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status' => 'required|in:active,expired,cancelled,pending',
            'paid_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string|max:255',
            'transaction_id' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'auto_renew' => 'boolean',
        ]);

        try {
            DB::beginTransaction();

            // Check if team already has an active subscription for the same package
            $existingSubscription = TeamSubscription::where('team_id', $team->id)
                ->where('service_package_id', $request->service_package_id)
                ->where('status', 'active')
                ->where('end_date', '>=', now())
                ->first();

            if ($existingSubscription) {
                return back()->withErrors(['error' => 'Team đã có gói dịch vụ này đang hoạt động.']);
            }

            TeamSubscription::create([
                'team_id' => $team->id,
                'service_package_id' => $request->service_package_id,
                'assigned_by' => Auth::id(),
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'status' => $request->status,
                'paid_amount' => $request->paid_amount,
                'payment_method' => $request->payment_method,
                'transaction_id' => $request->transaction_id,
                'notes' => $request->notes,
                'auto_renew' => $request->boolean('auto_renew'),
            ]);

            DB::commit();

            return redirect()->route('teams.show', $team)
                ->with('success', 'Gói dịch vụ đã được gán thành công cho team.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }

    /**
     * Get team subscriptions for a specific team
     */
    public function teamSubscriptions(Team $team)
    {
        $subscriptions = $team->subscriptions()
            ->with(['servicePackage', 'assignedBy'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('team-subscriptions.team-subscriptions', compact('team', 'subscriptions'));
    }
}
