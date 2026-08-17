<?php

namespace Tests\Feature;

use App\Http\Controllers\SalesReportController;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Shift;
use App\Models\User;
use App\Services\DashboardService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportingAggregationTest extends TestCase
{
    use RefreshDatabase;

    public function test_mixed_tax_snapshots_match_dashboard_and_sales_report(): void
    {
        [$order] = $this->createPaidOrder('2026-08-18 16:00:00');
        $order->items()->createMany([
            ['product_id' => $this->productId, 'product_name' => 'Taxable', 'quantity' => 1, 'price' => 55000, 'unit_cost' => 20000, 'tax_rate' => 10, 'tax_included' => true, 'taxable_base' => 50000, 'tax_amount' => 5000, 'subtotal' => 55000],
            ['product_id' => $this->productId, 'product_name' => 'Non-taxable', 'quantity' => 1, 'price' => 23636, 'unit_cost' => 8000, 'tax_rate' => 0, 'tax_included' => false, 'taxable_base' => 23636, 'tax_amount' => 1364, 'subtotal' => 23636],
        ]);

        $dashboard = app(DashboardService::class)->snapshot('2026-08-18');
        $report = app(SalesReportController::class)->getLaporanHarian('2026-08-18');

        $this->assertSame(73636.0, $dashboard['dpp']);
        $this->assertSame(6364.0, $dashboard['tax']);
        $this->assertSame(73636.0, $report['total_pendapatan_bersih']);
        $this->assertSame(6364.0, $report['total_pajak']);
    }

    public function test_2330_wib_transaction_belongs_only_to_that_jakarta_date(): void
    {
        [$order] = $this->createPaidOrder('2026-08-18 16:30:00'); // 23:30 WIB
        $order->items()->create(['product_id' => $this->productId, 'quantity' => 1, 'price' => 10000, 'unit_cost' => 3000, 'taxable_base' => 10000, 'tax_amount' => 0, 'subtotal' => 10000]);

        $this->assertSame(1, app(DashboardService::class)->snapshot('2026-08-18')['orderCount']);
        $this->assertSame(0, app(DashboardService::class)->snapshot('2026-08-19')['orderCount']);
        $this->assertSame(1, app(SalesReportController::class)->getLaporanHarian('2026-08-18')['total_transaksi']);
        $this->assertSame(0, app(SalesReportController::class)->getLaporanHarian('2026-08-19')['total_transaksi']);
    }

    private int $productId;

    private function createPaidOrder(string $createdAt): array
    {
        $user = User::factory()->create();
        $shift = Shift::create(['user_id' => $user->id, 'start_time' => $createdAt, 'initial_cash' => 0, 'status' => 'open']);
        $category = Category::create(['name' => uniqid('cat'), 'slug' => uniqid('cat-')]);
        $product = Product::create(['category_id' => $category->id, 'name' => uniqid('product'), 'price' => 10000, 'cost_price' => 3000, 'is_available' => true, 'stock' => 10, 'low_stock_threshold' => 1]);
        $this->productId = $product->id;
        $order = Order::create(['order_number' => uniqid('HLV-'), 'user_id' => $user->id, 'shift_id' => $shift->id, 'subtotal' => 10000, 'tax_amount' => 0, 'total_tax' => 0, 'total_amount' => 10000, 'payment_type' => 'CASH', 'net_received' => 10000, 'status' => 'paid', 'created_at' => CarbonImmutable::parse($createdAt, 'UTC')]);
        return [$order];
    }
}
