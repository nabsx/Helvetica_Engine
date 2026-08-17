<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Collection;

class ReceiptService
{
    public function payload(Order $order): array
    {
        $order->loadMissing('items.product', 'user');
        $taxSummary = $this->taxSummary($order->items);

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
                    'name' => $item->product_name ?: ($item->product?->name ?? 'Produk'),
                    'quantity' => $item->quantity,
                    'price' => (float) $item->price,
                    'subtotal' => (float) $item->subtotal,
                ])->values()->all(),
                'subtotal' => (float) $order->subtotal,
                'dpp' => $taxSummary['dpp'],
                'tax_amount' => (float) $order->tax_amount,
                'tax_summary' => $taxSummary['lines'],
                'rounding_adjustment' => (float) $order->rounding_adjustment,
                'total_amount' => (float) $order->total_amount,
                'cash_given' => $order->cash_given !== null ? (float) $order->cash_given : null,
                'change_amount' => $order->change_amount !== null ? (float) $order->change_amount : null,
            ],
        ];
    }

    private function taxSummary(Collection $items): array
    {
        $dpp = 0;
        $lines = [];

        foreach ($items as $item) {
            $gross = $this->cents($item->subtotal);
            $rate = $item->tax_rate;
            $hasRate = is_numeric($rate) && (float) $rate > 0;
            $rawIncluded = $item->getRawOriginal('tax_included');
            $included = $item->tax_included === true;
            $exclusive = $rawIncluded !== null && $item->tax_included === false;

            if ($included && $hasRate) {
                $rateBasisPoints = (int) round((float) $rate * 100);
                $lineDpp = (int) round($gross * 10000 / (10000 + $rateBasisPoints), 0, PHP_ROUND_HALF_UP);
                $lineTax = $gross - $lineDpp;
                $dpp += $lineDpp;
                $lines[] = $this->summaryLine($item, $lineDpp, $lineTax, true);
            } elseif ($hasRate && $exclusive && is_numeric($item->taxable_base) && is_numeric($item->tax_amount)) {
                $lineDpp = $this->cents($item->taxable_base);
                $dpp += $lineDpp;
                $lines[] = $this->summaryLine($item, $lineDpp, $this->cents($item->tax_amount), false);
            } else {
                $dpp += is_numeric($item->taxable_base) ? $this->cents($item->taxable_base) : $gross;
                $lines[] = ['label' => 'Pajak tidak tersedia', 'status' => 'unknown', 'dpp' => null, 'tax_amount' => null];
            }
        }

        return ['dpp' => $dpp / 100, 'lines' => $lines];
    }

    private function summaryLine(object $item, int $dpp, int $tax, bool $included): array
    {
        $name = $item->tax_name ?: ($item->tax_code ?: 'Pajak');
        $rate = rtrim(rtrim(number_format((float) $item->tax_rate, 2, '.', ''), '0'), '.');

        return [
            'label' => $included ? "Termasuk {$name} {$rate}%" : "{$name} {$rate}%",
            'status' => $included ? 'included' : 'exclusive',
            'dpp' => $dpp / 100,
            'tax_amount' => $tax / 100,
        ];
    }

    private function cents(mixed $amount): int
    {
        return (int) round((float) $amount * 100, 0, PHP_ROUND_HALF_UP);
    }
}
