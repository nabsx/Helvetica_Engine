<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\OrderItem;
use App\Services\ReceiptService;
use Illuminate\Support\Collection;
use Mockery;
use Tests\TestCase;

class ReceiptServiceTest extends TestCase
{
    public function test_included_tax_is_derived_from_gross_line_total(): void
    {
        $payload = $this->payload([$this->item(20000, 1, '10', true, 'PB1', 'PB1')]);

        $this->assertSame(18181.82, $payload['order']['dpp']);
        $this->assertSame('Termasuk PB1 10%', $payload['order']['tax_summary'][0]['label']);
        $this->assertSame(1818.18, $payload['order']['tax_summary'][0]['tax_amount']);
        $this->assertSame(21000.0, $payload['order']['total_amount']);
        $this->assertSame(22000.0, $payload['order']['cash_given']);
    }

    public function test_multiple_items_share_one_aggregated_included_tax_line(): void
    {
        $items = array_fill(0, 6, $this->item(15666.6666667, 1, '10', true, 'PB1', 'PB1'));
        $payload = $this->payload($items);

        $this->assertCount(1, $payload['order']['tax_summary']);
        $this->assertSame('Termasuk PB1 10%', $payload['order']['tax_summary'][0]['label']);
        $this->assertSame(8545.45, $payload['order']['tax_summary'][0]['tax_amount']);
        $this->assertSame(85454.55, $payload['order']['dpp']);
    }

    public function test_quantity_two_and_dynamic_rate_are_supported(): void
    {
        $payload = $this->payload([$this->item(40000, 2, '11', true, 'PPN', 'PPN')]);

        $this->assertSame(36036.04, $payload['order']['dpp']);
        $this->assertSame('Termasuk PPN 11%', $payload['order']['tax_summary'][0]['label']);
    }

    public function test_exclusive_tax_uses_persisted_snapshot_values(): void
    {
        $payload = $this->payload([$this->item(20000, 1, '10', false, 'PB1', 'PB1', 20000, 2000)]);

        $this->assertSame(20000.0, $payload['order']['dpp']);
        $this->assertSame(2000.0, $payload['order']['tax_summary'][0]['tax_amount']);
        $this->assertSame('PB1 10%', $payload['order']['tax_summary'][0]['label']);
    }

    public function test_legacy_metadata_does_not_invent_tax(): void
    {
        $payload = $this->payload([$this->item(20000, 1, null, null, null, null, null, null)]);

        $this->assertNull($payload['order']['tax_summary'][0]['tax_amount']);
        $this->assertSame('Pajak tidak tersedia', $payload['order']['tax_summary'][0]['label']);
    }

    private function payload(array $items): array
    {
        $order = Mockery::mock(Order::class)->makePartial();
        $order->order_number = 'HLV-TEST';
        $order->payment_type = 'CASH';
        $order->subtotal = 20000;
        $order->tax_amount = 0;
        $order->rounding_adjustment = 0;
        $order->total_amount = 21000;
        $order->cash_given = 22000;
        $order->change_amount = 1000;
        $order->shouldReceive('loadMissing')->once();
        $order->shouldReceive('getAttribute')->with('items')->andReturn(new Collection($items));
        $order->shouldReceive('getAttribute')->with('user')->andReturn(null);

        return app(ReceiptService::class)->payload($order);
    }

    private function item(float $subtotal, int $quantity, ?string $rate, ?bool $included, ?string $name, ?string $code, ?float $base = 0, ?float $tax = 0): OrderItem
    {
        $item = new OrderItem([
            'subtotal' => $subtotal,
            'quantity' => $quantity,
            'price' => $subtotal / $quantity,
            'tax_rate' => $rate,
            'tax_included' => $included,
            'tax_name' => $name,
            'tax_code' => $code,
            'taxable_base' => $base,
            'tax_amount' => $tax,
        ]);
        if ($included === null) {
            $item->setRawAttributes(array_merge($item->getAttributes(), ['tax_included' => null]));
        }

        return $item;
    }
}
