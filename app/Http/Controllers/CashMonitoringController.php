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

        $rows = $shifts->map(function (Shift $shift) use ($cashDrawer) {
            $expected = $cashDrawer->expected($shift);
            $cashSales = $cashDrawer->getCashSales($shift);
            $nonCashSales = round((float) $shift->orders()
                ->where('payment_type', 'QRIS')->where('status', 'paid')
                ->sum('total_amount'), 2);
            $txCount = (int) $shift->orders()->where('status', 'paid')->count();
            $physical = $shift->status === 'pending_close' ? (float) $shift->closing_cash : $expected;
            $lastActivity = optional($shift->cashMovements->sortByDesc('created_at')->first()?->created_at ?? $shift->start_time)
                ->timezone('Asia/Jakarta')->diffForHumans();

            return (object) [
                'shift' => $shift,
                'expected' => $expected,
                'physical' => $physical,
                'cashSales' => $cashSales,
                'nonCashSales' => $nonCashSales,
                'txCount' => $txCount,
                'difference' => $shift->cash_difference === null ? null : (float) $shift->cash_difference,
                'lastActivity' => $lastActivity,
            ];
        });

        $summary = (object) [
            'totalPhysical' => $rows->sum('physical'),
            'totalExpected' => $rows->sum('expected'),
            'pendingCount' => $rows->filter(fn ($row) => $row->shift->status === 'pending_close')->count(),
            'totalVariance' => $rows->sum(fn ($row) => $row->difference ?? 0),
            'totalCashSales' => $rows->sum('cashSales'),
            'totalNonCashSales' => $rows->sum('nonCashSales'),
            'totalTxCount' => $rows->sum('txCount'),
        ];

        $selectedShiftId = (int) $request->query('shift', 0);
        $selected = $rows->firstWhere(fn ($row) => $row->shift->id === $selectedShiftId)
            ?? $rows->firstWhere(fn ($row) => $row->shift->status === 'pending_close')
            ?? $rows->first();

        if ($selected) {
            $selected->shift->loadMissing(['cashMovements' => fn ($query) => $query->latest()]);
        }

        return view('admin.cash-monitoring', [
            'rows' => $rows,
            'summary' => $summary,
            'selected' => $selected,
            'cashDrawer' => $cashDrawer,
        ]);
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

    public function reject(Request $request, Shift $shift, CashDrawerService $cashDrawer, ActivityLogService $activityLogs): RedirectResponse
    {
        abort_unless($request->user()->can('reject', $shift), 403);

        $rejected = $cashDrawer->rejectShift($shift, (int) $request->user()->id);
        $activityLogs->record('shift.rejected', $rejected, [], $request);

        return back()->with('status', 'Shift dikembalikan ke kasir untuk hitung ulang.');
    }

    public function show(Request $request, Shift $shift, CashDrawerService $cashDrawer): View
    {
        abort_unless($request->user()->can('view-any-shift') || $request->user()->can('view', $shift), 403);
        $shift->load(['user', 'opener', 'cashMovements.user', 'orders' => fn ($query) => $query->latest()]);
        return view('admin.shift-detail', compact('shift', 'cashDrawer'));
    }
}