<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Services\CashDrawerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Services\ActivityLogService;

class CashMonitoringController extends Controller
{
    public function index(Request $request, CashDrawerService $cashDrawer): View
    {
        abort_unless($request->user()->can('view-cash-monitoring'), 403);
        $shifts = Shift::query()->with(['user', 'opener'])
            ->whereIn('status', ['open', 'pending_close'])
            ->latest('start_time')->get();

        return view('admin.cash-monitoring', compact('shifts', 'cashDrawer'));
    }

    public function approve(Request $request, Shift $shift, CashDrawerService $cashDrawer, ActivityLogService $activityLogs): RedirectResponse
    {
        abort_unless($request->user()->can('approve', $shift), 403);

        $approved = $cashDrawer->approveShift($shift, (int) $request->user()->id);
        $activityLogs->record('shift.approved', $approved, [
            'closing_cash' => $approved->closing_cash,
            'cash_difference' => $approved->cash_difference,
        ], $request);

        return back()->with('status', 'Shift berhasil di-approve.');
    }

    public function show(Request $request, Shift $shift, CashDrawerService $cashDrawer): View
    {
        abort_unless($request->user()->can('view-any-shift') || $request->user()->can('view', $shift), 403);
        $shift->load(['user', 'opener', 'cashMovements.user', 'orders' => fn ($query) => $query->latest()]);
        return view('admin.shift-detail', compact('shift', 'cashDrawer'));
    }
}
