<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderCancellationRequest;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderCancellationTest extends TestCase
{
    use RefreshDatabase;

    private function makePaidOrder(User $cashier, int $quantity = 2): Order
    {
        $category = Category::create(['name' => 'Coffee', 'slug' => 'coffee']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Espresso',
            'price' => 15000,
            'stock' => 10,
            'low_stock_threshold' => 2,
            'is_available' => true,
        ]);

        $shift = Shift::create([
            'user_id' => $cashier->id,
            'start_time' => now(),
            'initial_cash' => 100000,
            'status' => 'open',
        ]);

        $order = Order::create([
            'order_number' => 'HLV-TEST-'.uniqid(),
            'user_id' => $cashier->id,
            'shift_id' => $shift->id,
            'subtotal' => $product->price * $quantity,
            'tax_amount' => 0,
            'total_amount' => $product->price * $quantity,
            'payment_type' => 'CASH',
            'rounding_adjustment' => 0,
            'cash_given' => 100000,
            'change_amount' => 100000 - ($product->price * $quantity),
            'pg_fee' => 0,
            'net_received' => $product->price * $quantity,
            'status' => 'paid',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => $quantity,
            'price' => $product->price,
            'subtotal' => $product->price * $quantity,
        ]);

        // Mirror OrderController::store, which decrements stock at sale time.
        $product->decrement('stock', $quantity);

        return $order;
    }

    public function test_cashier_can_request_cancellation_for_their_own_order(): void
    {
        $cashier = User::create(['name' => 'Kasir Satu', 'pin' => '112233', 'role' => 'cashier']);
        $order = $this->makePaidOrder($cashier);

        $response = $this->actingAs($cashier)->postJson(
            "/orders/{$order->id}/cancellation-requests",
            ['reason' => 'Salah input menu, pelanggan batal beli.']
        );

        $response->assertStatus(201);
        $this->assertDatabaseHas('order_cancellation_requests', [
            'order_id' => $order->id,
            'requested_by' => $cashier->id,
            'status' => 'pending',
        ]);
        // The order itself must NOT change just because a request exists.
        $this->assertSame('paid', $order->fresh()->status);
    }

    public function test_reason_is_required_and_cannot_be_too_short(): void
    {
        $cashier = User::create(['name' => 'Kasir Satu', 'pin' => '112233', 'role' => 'cashier']);
        $order = $this->makePaidOrder($cashier);

        $this->actingAs($cashier)
            ->postJson("/orders/{$order->id}/cancellation-requests", ['reason' => 'ok'])
            ->assertStatus(422);
    }

    public function test_cashier_cannot_submit_a_second_pending_request_for_the_same_order(): void
    {
        $cashier = User::create(['name' => 'Kasir Satu', 'pin' => '112233', 'role' => 'cashier']);
        $order = $this->makePaidOrder($cashier);

        $this->actingAs($cashier)->postJson(
            "/orders/{$order->id}/cancellation-requests",
            ['reason' => 'Salah input menu.']
        )->assertStatus(201);

        $this->actingAs($cashier)->postJson(
            "/orders/{$order->id}/cancellation-requests",
            ['reason' => 'Coba lagi alasan lain.']
        )->assertStatus(422);

        $this->assertSame(1, OrderCancellationRequest::query()->where('order_id', $order->id)->count());
    }

    public function test_cashier_cannot_request_cancellation_for_another_cashiers_order(): void
    {
        $ownerCashier = User::create(['name' => 'Kasir Satu', 'pin' => '112233', 'role' => 'cashier']);
        $otherCashier = User::create(['name' => 'Kasir Dua', 'pin' => '445566', 'role' => 'cashier']);
        $order = $this->makePaidOrder($ownerCashier);

        $this->actingAs($otherCashier)->postJson(
            "/orders/{$order->id}/cancellation-requests",
            ['reason' => 'Mengaku-ngaku transaksi orang lain.']
        )->assertStatus(403);
    }

    public function test_admin_approval_cancels_the_order_and_restores_stock(): void
    {
        $admin = User::create(['name' => 'Admin', 'pin' => '123456', 'role' => 'admin']);
        $cashier = User::create(['name' => 'Kasir Satu', 'pin' => '112233', 'role' => 'cashier']);
        $order = $this->makePaidOrder($cashier, quantity: 2);
        $product = $order->items()->first()->product;
        $stockAfterSale = $product->fresh()->stock;

        $cancellationRequest = $order->cancellationRequests()->create([
            'requested_by' => $cashier->id,
            'reason' => 'Pelanggan komplain rasa, refund penuh.',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post(
            route('admin.cancellations.approve', $cancellationRequest),
            ['admin_note' => 'Disetujui, sesuai komplain pelanggan.']
        );

        $response->assertRedirect();
        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertSame('approved', $cancellationRequest->fresh()->status);
        $this->assertSame($admin->id, $cancellationRequest->fresh()->reviewed_by);
        $this->assertNotNull($cancellationRequest->fresh()->reviewed_at);
        // Stock must go back up by the cancelled order's quantity.
        $this->assertSame($stockAfterSale + 2, $product->fresh()->stock);
    }

    public function test_admin_rejection_leaves_the_order_untouched(): void
    {
        $admin = User::create(['name' => 'Admin', 'pin' => '123456', 'role' => 'admin']);
        $cashier = User::create(['name' => 'Kasir Satu', 'pin' => '112233', 'role' => 'cashier']);
        $order = $this->makePaidOrder($cashier);
        $product = $order->items()->first()->product;
        $stockAfterSale = $product->fresh()->stock;

        $cancellationRequest = $order->cancellationRequests()->create([
            'requested_by' => $cashier->id,
            'reason' => 'Coba-coba membatalkan transaksi sah.',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)->post(
            route('admin.cancellations.reject', $cancellationRequest),
            ['admin_note' => 'Transaksi valid, tidak ada bukti kesalahan.']
        )->assertRedirect();

        $this->assertSame('paid', $order->fresh()->status);
        $this->assertSame('rejected', $cancellationRequest->fresh()->status);
        // Stock must NOT change on a rejection.
        $this->assertSame($stockAfterSale, $product->fresh()->stock);
    }

    public function test_non_admin_cannot_approve_or_reject_cancellation_requests(): void
    {
        $cashier = User::create(['name' => 'Kasir Satu', 'pin' => '112233', 'role' => 'cashier']);
        $order = $this->makePaidOrder($cashier);

        $cancellationRequest = $order->cancellationRequests()->create([
            'requested_by' => $cashier->id,
            'reason' => 'Mencoba menyetujui pengajuan sendiri.',
            'status' => 'pending',
        ]);

        $this->actingAs($cashier)
            ->post(route('admin.cancellations.approve', $cancellationRequest))
            ->assertStatus(403);

        $this->assertSame('pending', $cancellationRequest->fresh()->status);
        $this->assertSame('paid', $order->fresh()->status);
    }
}
