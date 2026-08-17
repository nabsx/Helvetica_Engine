<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\PaymentGatewayConfig;
use App\Models\Product;
use App\Services\FinancialCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class FinancialCalculationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_qris_fee_is_merchant_deduction_not_customer_charge(): void
    {
        PaymentGatewayConfig::create(['payment_type' => 'QRIS', 'fee_rate' => 0.7, 'fee_flat' => 0, 'fee_mode' => 'percentage_plus_flat', 'fee_basis' => 'subtotal_plus_tax', 'is_active' => true]);
        $category = Category::create(['name' => 'Test', 'slug' => 'test']);
        $product = Product::create(['category_id' => $category->id, 'name' => 'Coffee', 'price' => 10000, 'cost_price' => 3000, 'tax_rate' => 0, 'tax_included' => false, 'is_available' => true, 'stock' => 10, 'low_stock_threshold' => 1]);
        $result = app(FinancialCalculationService::class)->calculate(new Collection([$product->id => $product]), [['product_id' => $product->id, 'quantity' => 1]], 'QRIS');

        $this->assertSame('10000.00', $result['total_amount']);
        $this->assertSame('70.00', $result['gateway_fee_amount']);
        $this->assertSame('9930.00', $result['net_received']);
    }
}
