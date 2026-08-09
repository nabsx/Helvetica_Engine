<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderCancellationRequest;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminCancellationController extends Controller
{
    public function index(): View
    {
        return view('admin.cancellations.index', [
            'pending' => OrderCancellationRequest::query()
                ->with(['order.items', 'requestedBy'])
                ->pending()
                ->orderBy('created_at')
                ->get(),
            'resolved' => OrderCancellationRequest::query()
                ->with(['order', 'requestedBy', 'reviewedBy'])
                ->whereIn('status', ['approved', 'rejected'])
                ->latest('reviewed_at')
                ->limit(30)
                ->get(),
        ]);
    }

    /**
     * Approving is the only path that actually cancels the order. Stock is
     * restored line-by-line with the same row-locking pattern OrderController
     * uses when it deducts stock, so a restock can never race a concurrent sale.
     */
    public function approve(Request $request, OrderCancellationRequest $cancellationRequest): RedirectResponse
    {
        $data = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:500'],
        ]);

        if ($cancellationRequest->status !== 'pending') {
            return back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        DB::transaction(function () use ($cancellationRequest, $data) {
            $order = Order::query()->whereKey($cancellationRequest->order_id)->lockForUpdate()->first();

            if ($order->status !== 'paid') {
                abort(422, 'Transaksi ini sudah tidak berstatus paid, tidak bisa dibatalkan.');
            }

            $order->update(['status' => 'cancelled']);

            $items = $order->items()->get();
            $products = Product::query()
                ->whereIn('id', $items->pluck('product_id'))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($items as $item) {
                $products->get($item->product_id)?->increment('stock', $item->quantity);
            }

            $cancellationRequest->update([
                'status' => 'approved',
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
                'admin_note' => $data['admin_note'] ?? null,
            ]);
        });

        return back()->with('success', 'Transaksi #'.$cancellationRequest->order->order_number.' berhasil dibatalkan dan stok dikembalikan.');
    }

    /**
     * Rejecting leaves the order untouched — it stays 'paid' and keeps
     * counting toward the cashier's expected cash. Only the request record
     * is marked as reviewed, so there's a permanent trail of who asked and
     * who said no, and why.
     */
    public function reject(Request $request, OrderCancellationRequest $cancellationRequest): RedirectResponse
    {
        $data = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:500'],
        ]);

        if ($cancellationRequest->status !== 'pending') {
            return back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $cancellationRequest->update([
            'status' => 'rejected',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'admin_note' => $data['admin_note'] ?? null,
        ]);

        return back()->with('success', 'Pengajuan pembatalan ditolak.');
    }
}
