
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    private const TAX_DIVISOR = 1.10;
    private const QRIS_MDR_RATE = 0.007;

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $data = $request->validated();
        $activeShift = $request->attributes->get('active_shift');

        if (! $activeShift) {
            return response()->json([
                'message' => 'Tidak ada shift aktif. Silakan buka shift terlebih dahulu.',
            ], 422);
        }
        $productIds = collect($data['items'])->pluck('product_id');

        $result = DB::transaction(function () use ($data, $productIds, $activeShift): Order {
            $products = Product::query()->whereIn('id', $productIds)->lockForUpdate()->get()->keyBy('id');
            $lineItems = [];
            $subtotal = 0;

            foreach ($data['items'] as $item) {
                $product = $products->get($item['product_id']);
                $quantity = (int) $item['quantity'];

                if (! $product || ! $product->is_available || $product->stock < $quantity) {
                    abort(422, $product ? "Stok {$product->name} tidak mencukupi." : 'Produk tidak tersedia.');
                }

                $lineTotal = $quantity * (float) $product->price;
                $subtotal += $lineTotal;
                $lineItems[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $quantity,
                    'unit_price' => $product->price,
                    'line_total' => $lineTotal,
                ];
            }

            $dpp = round($subtotal / self::TAX_DIVISOR, 2);
            $tax = round($subtotal - $dpp, 2);
            $rounding = $data['payment_type'] === 'CASH' ? round($subtotal / 500) * 500 - $subtotal : 0;
            $total = $subtotal + $rounding;
            $cashGiven = $data['payment_type'] === 'CASH' ? (float) ($data['cash_given'] ?? 0) : null;
            $change = $cashGiven === null ? null : $cashGiven - $total;

            if ($cashGiven !== null && $change < 0) {
                abort(422, 'Uang tunai kurang dari total transaksi.');
            }

            $orderNumber = 'HLV-'.now()->format('YmdHis').'-'.strtoupper(bin2hex(random_bytes(3)));

            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => Auth::id(),
                'shift_id' => $activeShift->id,
                'subtotal' => $subtotal,
                'tax_amount' => $tax,
                'total_amount' => $total,
                'payment_type' => $data['payment_type'],
                'rounding_adjustment' => $rounding,
                'cash_given' => $cashGiven,
                'change_amount' => $change,
                'pg_fee' => $data['payment_type'] === 'QRIS' ? round($total * self::QRIS_MDR_RATE, 2) : 0,
                'net_received' => $total,
                'status' => 'paid',
            ]);

            $order->items()->createMany($lineItems);

            foreach ($data['items'] as $item) {
                $products->get($item['product_id'])->decrement('stock', (int) $item['quantity']);
            }

            return $order->load('items');
        });

        return response()->json([
            'message' => 'Transaksi berhasil disimpan.',
            'order' => $result,
            'change' => $result->change_amount,
        ], 201);
    }
}
