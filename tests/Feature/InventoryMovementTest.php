<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Shift;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class InventoryMovementTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(int $stock = 10): Product
    {
        $category = Category::create(['name' => 'Coffee', 'slug' => 'coffee']);

        return Product::create([
            'category_id' => $category->id,
            'name' => 'Espresso',
            'price' => 15000,
            'stock' => $stock,
            'low_stock_threshold' => 2,
            'is_available' => true,
        ]);
    }

    private function openShiftFor(User $cashier): Shift
    {
        return Shift::create([
            'user_id' => $cashier->id,
            'start_time' => now(),
            'initial_cash' => 100000,
            'status' => 'open',
        ]);
    }

    public function test_checkout_writes_a_sale_movement_and_moves_stock_together(): void
    {
        $cashier = User::create(['name' => 'Kasir Satu', 'pin' => '112233', 'role' => 'cashier']);
        $product = $this->makeProduct(stock: 10);
        $this->openShiftFor($cashier);

        $response = $this->actingAs($cashier)->postJson('/orders', [
            'items' => [['product_id' => $product->id, 'quantity' => 3]],
            'payment_type' => 'CASH',
            'cash_given' => 100000,
        ]);

        $response->assertStatus(201);

        $product->refresh();
        $this->assertSame(7, $product->stock);

        $movement = InventoryMovement::query()->where('product_id', $product->id)->where('type', 'sale')->sole();
        $this->assertSame(-3, $movement->quantity_delta);
        $this->assertSame(10, $movement->stock_before);
        $this->assertSame(7, $movement->stock_after);
        $this->assertSame($cashier->id, $movement->user_id);
    }

    public function test_approved_cancellation_writes_a_refund_movement(): void
    {
        $admin = User::create(['name' => 'Admin', 'pin' => '123456', 'role' => 'admin']);
        $cashier = User::create(['name' => 'Kasir Satu', 'pin' => '112233', 'role' => 'cashier']);
        $product = $this->makeProduct(stock: 10);
        $shift = $this->openShiftFor($cashier);

        $checkout = $this->actingAs($cashier)->postJson('/orders', [
            'items' => [['product_id' => $product->id, 'quantity' => 2]],
            'payment_type' => 'CASH',
            'cash_given' => 100000,
        ])->json('order');

        $order = \App\Models\Order::find($checkout['id']);
        $product->refresh();
        $this->assertSame(8, $product->stock);

        $cancellationRequest = $order->cancellationRequests()->create([
            'requested_by' => $cashier->id,
            'reason' => 'Pelanggan komplain rasa, refund penuh.',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)->post(
            route('admin.cancellations.approve', $cancellationRequest),
            ['admin_note' => 'Disetujui.']
        )->assertRedirect();

        $product->refresh();
        $this->assertSame(10, $product->stock);

        $movement = InventoryMovement::query()->where('product_id', $product->id)->where('type', 'refund')->sole();
        $this->assertSame(2, $movement->quantity_delta);
        $this->assertSame(8, $movement->stock_before);
        $this->assertSame(10, $movement->stock_after);
    }

    public function test_inventory_movements_are_append_only(): void
    {
        $user = User::create(['name' => 'Admin', 'pin' => '123456', 'role' => 'admin']);
        $product = $this->makeProduct();

        $movement = app(InventoryService::class)->recordAdjustment($product->fresh(), 20, 'Stock opname', $user->id);

        $this->expectException(\LogicException::class);
        $movement->update(['note' => 'Mencoba mengubah histori']);
    }

    public function test_inventory_movements_cannot_be_deleted(): void
    {
        $user = User::create(['name' => 'Admin', 'pin' => '123456', 'role' => 'admin']);
        $product = $this->makeProduct();

        $movement = app(InventoryService::class)->recordAdjustment($product->fresh(), 20, 'Stock opname', $user->id);

        $this->expectException(\LogicException::class);
        $movement->delete();
    }

    public function test_concurrent_sales_never_push_stock_negative(): void
    {
        $cashierA = User::create(['name' => 'Kasir A', 'pin' => '111111', 'role' => 'cashier']);
        $cashierB = User::create(['name' => 'Kasir B', 'pin' => '222222', 'role' => 'cashier']);
        $product = $this->makeProduct(stock: 1);
        $this->openShiftFor($cashierA);
        $this->openShiftFor($cashierB);

        // sqlite (used in tests) serializes writers, so this exercises the
        // same code path the lockForUpdate()-guarded transaction in
        // OrderController::store() relies on against real concurrency in MySQL:
        // the second sale on the last unit must be rejected, not oversell it.
        $first = $this->actingAs($cashierA)->postJson('/orders', [
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
            'payment_type' => 'CASH',
            'cash_given' => 100000,
        ]);
        $second = $this->actingAs($cashierB)->postJson('/orders', [
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
            'payment_type' => 'CASH',
            'cash_given' => 100000,
        ]);

        $this->assertSame(201, $first->status());
        $this->assertSame(422, $second->status());
        $this->assertSame(0, $product->fresh()->stock);
    }
}
