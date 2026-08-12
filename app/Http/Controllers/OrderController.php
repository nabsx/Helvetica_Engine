<?php

namespace App\Http\Controllers;

use App\Http\Requests\CalculateOrderRequest;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use App\Models\Product;
use App\Services\ActivityLogService;
use App\Services\FinancialCalculationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $activityLogs,
        private readonly FinancialCalculationService $financials,
    ) {}

    /**
     * Live total preview for the cashier's cart. Uses the exact same
     * FinancialCalculationService as store() so what the cashier sees on
     * screen always matches what gets saved and printed on the receipt —
     * there is no separate tax/rounding logic duplicated in the frontend.
     */
    public function calculate(CalculateOrderRequest $request): JsonResponse
    {
        $data = $request->validated();

        $productIds = collect($data['items'])->pluck('product_id')->unique()->values();
        $products = Product::query()->whereIn('id', $productIds)->get()->keyBy('id');

        foreach ($data['items'] as $item) {
            if (! $products->has((int) $item['product_id'])) {
                abort(422, 'Produk tidak ditemukan.');
            }
        }

        $financials = $this->financials->calculate($products, $data['items'], $data['payment_type']);

        return response()->json([
            'subtotal' => (float) $financials['subtotal'],
            'tax_amount' => (float) $financials['total_tax'],
            'gateway_fee_amount' => (float) $financials['gateway_fee_amount'],
            // store() always persists 0 for rounding_adjustment, so the
            // preview must match that exactly instead of guessing its own
            // rounding rule (that mismatch was the original bug).
            'rounding_adjustment' => 0,
            'total_amount' => (float) $financials['total_amount'],
        ]);
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $data = $request->validated();
        $activeShift = $request->attributes->get('active_shift');

        if (! $activeShift) {
            return response()->json(['message' => 'Tidak ada shift aktif. Silakan buka shift terlebih dahulu.'], 422);
        }

        $productIds = collect($data['items'])->pluck('product_id')->unique()->values();
        $result = DB::transaction(function () use ($data, $productIds, $activeShift): Order {
            $products = Product::query()->whereIn('id', $productIds)->lockForUpdate()->get()->keyBy('id');
            foreach ($data['items'] as $item) {
                $product = $products->get((int) $item['product_id']);
                if (! $product || ! $product->is_available || $product->stock < (int) $item['quantity']) {
                    abort(422, $product ? "Stok {$product->name} tidak mencukupi." : 'Produk tidak tersedia.');
                }
            }

            $financials = $this->financials->calculate($products, $data['items'], $data['payment_type']);
            $total = (float) $financials['total_amount'];
            $cashGiven = $data['payment_type'] === 'CASH' ? (float) ($data['cash_given'] ?? 0) : null;
            $change = $cashGiven === null ? null : round($cashGiven - $total, 2);
            if ($cashGiven !== null && $change < 0) {
                abort(422, 'Uang tunai kurang dari total transaksi.');
            }

            $config = $financials['gateway_config'];
            $order = Order::create([
                'order_number' => 'HLV-'.now()->format('YmdHis').'-'.strtoupper(bin2hex(random_bytes(3))),
                'user_id' => Auth::id(), 'shift_id' => $activeShift->id,
                'subtotal' => $financials['subtotal'], 'tax_amount' => $financials['total_tax'],
                'total_tax' => $financials['total_tax'], 'total_amount' => $financials['total_amount'],
                'payment_type' => $data['payment_type'], 'rounding_adjustment' => 0,
                'cash_given' => $cashGiven, 'change_amount' => $change,
                'pg_fee' => $financials['gateway_fee_amount'],
                'gateway_fee_rate' => $config?->fee_rate ?? 0, 'gateway_fee_flat' => $config?->fee_flat ?? 0,
                'gateway_fee_amount' => $financials['gateway_fee_amount'],
                'gateway_fee_mode' => $config?->fee_mode ?? 'none', 'gateway_fee_basis' => $config?->fee_basis ?? 'none',
                'net_received' => $financials['net_received'], 'status' => 'paid',
            ]);
            $order->items()->createMany($financials['items']);
            foreach ($data['items'] as $item) {
                $products->get((int) $item['product_id'])->decrement('stock', (int) $item['quantity']);
            }
            $order = $order->load('items');
            $this->activityLogs->record('order.created', $order, ['total_amount' => $order->total_amount, 'payment_type' => $order->payment_type]);
            return $order;
        });

        return response()->json(['message' => 'Transaksi berhasil disimpan.', 'order' => $result, 'change' => $result->change_amount], 201);
    }
}
