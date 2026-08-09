<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderCancellationRequest;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderCancellationController extends Controller
{
    /**
     * Cashier submits a cancellation request. This never touches the
     * order's status — it only queues the request for an admin to review
     * in the admin panel (see AdminCancellationController). This is the
     * whole point: a cashier alone can never make a sale disappear from
     * the shift's cash reconciliation.
     */
    public function store(StoreOrderCancellationRequest $request, Order $order): JsonResponse
    {
        $user = Auth::user();

        // A cashier may only request cancellation for their own sale;
        // an admin may request one for any order (e.g. reviewing on a
        // cashier's behalf). This stops one cashier from being able to
        // touch another cashier's transactions at all.
        if (! $user->isAdmin() && $order->user_id !== $user->id) {
            return response()->json([
                'message' => 'Anda hanya bisa mengajukan pembatalan untuk transaksi Anda sendiri.',
            ], 403);
        }

        if ($order->status !== 'paid') {
            return response()->json([
                'message' => 'Transaksi ini sudah tidak berstatus paid, tidak bisa diajukan pembatalan.',
            ], 422);
        }

        $cancellationRequest = DB::transaction(function () use ($request, $order, $user) {
            // Lock the order row so two simultaneous submissions (double
            // tap, two tabs) can't both slip past the "already pending" check.
            $lockedOrder = Order::query()->whereKey($order->id)->lockForUpdate()->first();

            if ($lockedOrder->hasPendingCancellationRequest()) {
                abort(422, 'Transaksi ini sudah punya pengajuan pembatalan yang masih menunggu persetujuan admin.');
            }

            return $lockedOrder->cancellationRequests()->create([
                'requested_by' => $user->id,
                'reason' => $request->validated('reason'),
                'status' => 'pending',
            ]);
        });

        return response()->json([
            'message' => 'Pengajuan pembatalan terkirim. Menunggu persetujuan admin.',
            'cancellation_request' => $cancellationRequest,
        ], 201);
    }
}
