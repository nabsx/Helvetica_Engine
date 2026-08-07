<?php

namespace App\Services;

use App\Models\Order;

class ReceiptService
{
    public function payload(Order $order): array
    {
        $order->loadMissing('items.product', 'user');

        return [
            'store' => [
                'name' => config('receipt.store.name'),
                'address' => config('receipt.store.address'),
                'phone' => config('receipt.store.phone'),
            ],
            'order' => [
                'number' => $order->order_number,
                'date' => $order->created_at?->format('d/m/Y H:i'),
                'cashier' => $order->user?->name,
                'payment_type' => $order->payment_type,
                'items' => $order->items->map(fn ($item): array => [
                    'name' => $item->product?->name ?? 'Produk',
                    'quantity' => $item->quantity,
                    'price' => (float) $item->price,
                    'subtotal' => (float) $item->subtotal,
                ])->values()->all(),
                'subtotal' => (float) $order->subtotal,
                'dpp' => round((float) $order->subtotal / 1.10, 2),
                'tax_amount' => (float) $order->tax_amount,
                'rounding_adjustment' => (float) $order->rounding_adjustment,
                'total_amount' => (float) $order->total_amount,
                'cash_given' => $order->cash_given !== null ? (float) $order->cash_given : null,
                'change_amount' => $order->change_amount !== null ? (float) $order->change_amount : null,
            ],
        ];
    }
}
