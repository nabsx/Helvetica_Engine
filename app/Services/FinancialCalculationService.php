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

            // PB1 snapshot for this line — independent of how total_amount is
            // built below. When the price is tax-inclusive, the tax is baked
            // into $lineNetCents already, so it must be extracted (back-
            // calculated) rather than reported as 0. When exclusive, the tax
            // is calculated on top of the base as before. This mirrors the
            // formula ReceiptService::taxSummary() already uses at render
            // time, so stored snapshots and printed receipts agree.
            if ($product->tax_included) {
                $rateBasisPoints = (int) round(((float) $product->tax_rate) * 100);
                $taxableBaseCents = $rateBasisPoints > 0
                    ? (int) round($lineNetCents * 10000 / (10000 + $rateBasisPoints), 0, PHP_ROUND_HALF_UP)
                    : $lineNetCents;
                $lineTaxCents = $lineNetCents - $taxableBaseCents;
            } else {
                $taxableBaseCents = $lineNetCents;
                $lineTaxCents = $this->percentageCents($taxableBaseCents, (string) $product->tax_rate);
            }

            $subtotalCents += $lineNetCents;
            // Order-level total_tax/total_amount math is intentionally
            // unchanged: for tax-inclusive lines the tax is already inside
            // $lineNetCents (and thus $subtotalCents), so it must NOT be
            // added again here or total_amount would double-count it. Only
            // exclusive-tax lines add their tax on top, exactly as before.
            $taxCents += $product->tax_included ? 0 : $lineTaxCents;
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
        $customerTotalCents = $subtotalCents + $taxCents;

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
