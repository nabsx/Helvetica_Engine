<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /** Pajak Restoran (PBJT) */
    private const TAX_RATE = 0.10;

    /** Standard QRIS MDR (e.g. Midtrans) */
    private const QRIS_MDR_RATE = 0.007;

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Injected by the EnsureShiftIsOpen middleware — guarantees this
        // request only reaches here if the cashier has an open shift.
        $shift = $request->attributes->get('active_shift');

        // Re-fetch prices from the DB — never trust prices sent by the
        // client, otherwise a tampered request could under-charge.
        $productIds = collect($data['items'])->pluck('product_id');
        $products = Product::whereIn('id', $productIds)->available()->get()->keyBy('id');

        foreach ($data['items'] as $item) {
            if (! $products->has($item['product_id'])) {
                return response()->json([
                    'message' => "Produk dengan ID {$item['product_id']} tidak tersedia.",
                ], 422);
            }
        }

        // ---- 1. Pricing calculation (pure computation, no DB writes yet) ----
        $subtotal = 0;
        $lineItems = [];

        foreach ($data['items'] as $item) {
            $product = $products[$item['product_id']];
            $lineSubtotal = $product->price * $item['quantity'];
            $subtotal += $lineSubtotal;

            $lineItems[] = [
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'price' => $product->price,
                'subtotal' => $lineSubtotal,
            ];
        }

        $taxAmount = round($subtotal * self::TAX_RATE, 2);
        $totalAmount = $subtotal + $taxAmount;

        // QRIS eats a merchant discount rate fee; cash doesn't.
        $pgFee = $data['payment_type'] === 'QRIS'
            ? round($totalAmount * self::QRIS_MDR_RATE, 2)
            : 0.0;

        $netReceived = $totalAmount - $pgFee;

        // ---- 2. Validate cash sufficiency BEFORE touching the DB ----
        $change = null;
        if ($data['payment_type'] === 'CASH') {
            $change = round(((float) $data['cash_given']) - $totalAmount, 2);

            if ($change < 0) {
                return response()->json([
                    'message' => 'Uang yang dibayarkan kurang dari total tagihan.',
                ], 422);
            }
        }

        // ---- 3. Persist atomically: order header + all line items together ----
        $order = DB::transaction(function () use ($lineItems, $subtotal, $taxAmount, $totalAmount, $data, $pgFee, $netReceived, $shift) {
            /** @var \App\Models\Order $order */
            $order = Order::create([
                'order_number' => 'TEMP', // placeholder, finalized right below
                'user_id' => Auth::id(),
                'shift_id' => $shift->id,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'payment_type' => $data['payment_type'],
                'pg_fee' => $pgFee,
                'net_received' => $netReceived,
                'status' => 'paid',
            ]);

            // Build the invoice number from the row's own auto-increment id
            // (guaranteed unique by the DB) instead of counting existing
            // rows, which would race under concurrent requests.
            $order->update([
                'order_number' => Order::formatOrderNumber($order->id),
            ]);

            $order->items()->createMany($lineItems);

            return $order;
        });

        return response()->json([
            'message' => 'Transaksi berhasil.',
            'order' => $order->load('items.product'),
            'change' => $change,
        ], 201);
    }
}
