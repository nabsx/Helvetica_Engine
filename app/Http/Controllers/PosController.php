<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Services\DashboardService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PosController extends Controller
{
    public function index(): View
    {
        $categories = Category::with(['products' => fn ($q) => $q->available()])
            ->whereHas('products', fn ($q) => $q->available())
            ->get();

        $activeShift = Auth::user()->shifts()->open()->latest('start_time')->first();

        // Recent transactions from THIS shift only, so a cashier can pick
        // one to request a cancellation for. Whether it's still eligible
        // (paid, no pending request yet) is flagged here rather than
        // recomputed in Alpine, so the frontend stays dumb about the rules.
        $recentOrders = $activeShift
            ? $activeShift->orders()
                ->with(['cancellationRequests' => fn ($q) => $q->pending()])
                ->latest()
                ->limit(20)
                ->get()
                ->map(fn ($order) => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'total_amount' => $order->total_amount,
                    'status' => $order->status,
                    'created_at' => $order->created_at->timezone(DashboardService::OPERATIONAL_TIMEZONE)->format('H:i'),
                    'can_request_cancellation' => $order->status === 'paid' && $order->cancellationRequests->isEmpty(),
                    'has_pending_cancellation' => $order->status === 'paid' && $order->cancellationRequests->isNotEmpty(),
                ])
            : collect();

        return view('pos.index', compact('categories', 'activeShift', 'recentOrders'));
    }
}
