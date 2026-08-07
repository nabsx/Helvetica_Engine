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
    /** Harga produk sudah termasuk PB1 10%. */
    private const TAX_DIVISOR = 1.10;

    /** Standard QRIS MDR. */
    private const QRIS_MDR_RATE = 0.007;

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $data = $request->validated();
        $shift = $request->attributes->get('active_shift');
        $products = Product::whereIn('id', collect($data['items'])->pluck('product_id'))
            ->available()
            ->get()
            ->keyBy('id');

        foreach ($data['items'] as $item) {
            if (! $products->has($item['product_id'])) {
                return response()->json([
                    'message' => "Produk dengan ID {$item['product_id']} tidak tersedia.",
                ], 422);
            }
        }

        $lineItems = [];
        $totalBelanja = 0.0;

        foreach ($data['items'] as $item) {
            $product = $products[$item['product_id']];
            $lineSubtotal = round((float) $product->price * (int) $item['quantity'], 2);
            $totalBelanja += $lineSubtotal;

            $lineItems[] = [
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'price' => $product->price,
                'subtotal' => $lineSubtotal,
            ];
        }

        $totalBelanja = round($totalBelanja, 2);
        $dpp = round($totalBelanja / self::TAX_DIVISOR, 2);
        $pajak = round($totalBelanja - $dpp, 2);
        $roundingAdjustment = $this->calculateRoundingAdjustment(
            $totalBelanja,
            $data['payment_type']
        );
        $totalBayar = round($totalBelanja + $roundingAdjustment, 2);

        $pgFee = $data['payment_type'] === 'QRIS'
            ? round($totalBayar * self::QRIS_MDR_RATE, 2)
            : 0.0;
        $netReceived = round($totalBayar - $pgFee, 2);

        $change = null;
        if ($data['payment_type'] === 'CASH') {
            $change = round((float) $data['cash_given'] - $totalBayar, 2);

            if ($change < 0) {
                return response()->json([
                    'message' => 'Uang yang dibayarkan kurang dari total tagihan.',
                ], 422);
            }
        }

        $order = DB::transaction(function () use (
            $lineItems,
            $totalBelanja,
            $pajak,
            $totalBayar,
            $roundingAdjustment,
            $data,
            $pgFee,
            $netReceived,
            $change,
            $shift
        ): Order {
            $order = Order::create([
                'order_number' => 'TEMP',
                'user_id' => Auth::id(),
                'shift_id' => $shift->id,
                'subtotal' => $totalBelanja,
                'tax_amount' => $pajak,
                'total_amount' => $totalBayar,
                'payment_type' => $data['payment_type'],
                'rounding_adjustment' => $roundingAdjustment,
                'cash_given' => $data['payment_type'] === 'CASH' ? $data['cash_given'] : null,
                'change_amount' => $change,
                'pg_fee' => $pgFee,
                'net_received' => $netReceived,
                'status' => 'paid',
            ]);

            $order->update([
                'order_number' => Order::formatOrderNumber($order->id),
            ]);
            $order->items()->createMany($lineItems);

            return $order;
        });

        return response()->json([
            'message' => 'Transaksi berhasil.',
            'order' => $order->load('items.product'),
            'calculation' => [
                'total_belanja' => $totalBelanja,
                'dpp' => $dpp,
                'pajak' => $pajak,
                'rounding_adjustment' => $roundingAdjustment,
                'total_bayar' => $totalBayar,
            ],
            'change' => $change,
        ], 201);
    }

    private function calculateRoundingAdjustment(float $totalBelanja, string $paymentType): float
    {
        if ($paymentType !== 'CASH') {
            return 0.0;
        }

        return round((round($totalBelanja / 500) * 500) - $totalBelanja, 2);
    }
}
