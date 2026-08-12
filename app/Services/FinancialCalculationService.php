<?php

namespace App\Services;

use App\Models\PaymentGatewayConfig;
use App\Models\Product;
use Illuminate\Support\Collection;

class FinancialCalculationService
{
    public function calculate(Collection $products, array $items, string $paymentType): array
    {
        $lines = [];
        $subtotalCents = 0;
        $taxCents = 0;

        foreach ($items as $item) {
            $product = $products->get((int) $item['product_id']);
            $quantity = (int) $item['quantity'];
            $unitPriceCents = $this->cents($product->price);
            $lineNetCents = $unitPriceCents * $quantity;
            $taxableBaseCents = $lineNetCents;
            $lineTaxCents = $product->tax_included
                ? 0
                : $this->percentageCents($taxableBaseCents, (string) $product->tax_rate);

            $subtotalCents += $lineNetCents;
            $taxCents += $lineTaxCents;
            $lines[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity' => $quantity,
                'price' => $this->money($unitPriceCents),
                'unit_cost' => $product->cost_price,
                'subtotal' => $this->money($lineNetCents),
                'tax_name' => $product->tax_name,
                'tax_code' => $product->tax_code,
                'tax_rate' => $product->tax_rate,
                'tax_included' => $product->tax_included,
                'taxable_base' => $this->money($taxableBaseCents),
                'tax_amount' => $this->money($lineTaxCents),
            ];
        }

        $config = $paymentType === 'CASH'
            ? null
            : PaymentGatewayConfig::query()->firstOrCreate(
                ['payment_type' => $paymentType],
                ['fee_rate' => 0.7, 'fee_flat' => 0, 'fee_mode' => 'percentage_plus_flat', 'fee_basis' => 'subtotal_plus_tax', 'is_active' => true],
            );
        if ($config && ! $config->is_active) {
            $config = null;
        }
        $basisCents = match ($config?->fee_basis) {
            'subtotal' => $subtotalCents,
            'tax' => $taxCents,
            default => $subtotalCents + $taxCents,
        };
        $feeCents = $config
            ? $this->percentageCents($basisCents, (string) $config->fee_rate) + $this->cents($config->fee_flat)
            : 0;
        $customerTotalCents = $subtotalCents + $taxCents + $feeCents;

        return [
            'items' => $lines,
            'subtotal' => $this->money($subtotalCents),
            'total_tax' => $this->money($taxCents),
            'gateway_fee_amount' => $this->money($feeCents),
            'gateway_config' => $config,
            'total_amount' => $this->money($customerTotalCents),
            'net_received' => $this->money($customerTotalCents - $feeCents),
        ];
    }

    private function cents(string|int|float $amount): int
    {
        return (int) round(((float) $amount) * 100, 0, PHP_ROUND_HALF_UP);
    }

    private function money(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
    }

    private function percentageCents(int $cents, string $rate): int
    {
        $normalized = number_format((float) $rate, 4, '.', '');
        $rateScaled = (int) str_replace('.', '', $normalized);
        return intdiv(($cents * $rateScaled) + 500000, 1000000);
    }
}
