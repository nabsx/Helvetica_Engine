<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\Product;

class InventoryService
{
    /**
     * Record a sale deduction. Expects the product to already be locked
     * for update by the caller within a DB::transaction context.
     *
     * @param Product $product Already locked product instance
     * @param int $quantity Quantity to deduct (positive)
     * @param Order $order Order that triggered the sale
     * @param int $userId User ID who performed the sale
     * @return InventoryMovement
     */
    public function recordSale(Product $product, int $quantity, Order $order, int $userId): InventoryMovement
    {
        $stockBefore = $product->stock;
        $stockAfter = $stockBefore - $quantity;

        $product->forceFill(['stock' => $stockAfter])->save();

        return InventoryMovement::create([
            'product_id' => $product->id,
            'type' => 'sale',
            'quantity_delta' => -$quantity,
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
            'reference_type' => Order::class,
            'reference_id' => $order->id,
            'user_id' => $userId,
        ]);
    }

    /**
     * Record a refund restoration. Expects the product to already be locked
     * for update by the caller within a DB::transaction context.
     *
     * @param Product $product Already locked product instance
     * @param int $quantity Quantity to restore (positive)
     * @param Order $order Order that is being refunded
     * @param int $userId User ID who approved the refund
     * @return InventoryMovement
     */
    public function recordRefund(Product $product, int $quantity, Order $order, int $userId): InventoryMovement
    {
        $stockBefore = $product->stock;
        $stockAfter = $stockBefore + $quantity;

        $product->forceFill(['stock' => $stockAfter])->save();

        return InventoryMovement::create([
            'product_id' => $product->id,
            'type' => 'refund',
            'quantity_delta' => $quantity,
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
            'reference_type' => Order::class,
            'reference_id' => $order->id,
            'user_id' => $userId,
        ]);
    }

    /**
     * Record a manual adjustment (admin opname/koreksi stok).
     * This method DOES acquire its own lock since it's typically
     * called from outside a transaction context.
     *
     * @param Product $product Product to adjust
     * @param int $newStock New stock level
     * @param string $note Reason for adjustment
     * @param int $userId User ID who made the adjustment
     * @return InventoryMovement
     */
    public function recordAdjustment(Product $product, int $newStock, string $note, int $userId): InventoryMovement
    {
        $locked = Product::query()
            ->whereKey($product->id)
            ->lockForUpdate()
            ->firstOrFail();

        $stockBefore = $locked->stock;
        $quantityDelta = $newStock - $stockBefore;
        $stockAfter = $newStock;

        $locked->forceFill(['stock' => $stockAfter])->save();

        return InventoryMovement::create([
            'product_id' => $locked->id,
            'type' => 'adjustment',
            'quantity_delta' => $quantityDelta,
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
            'user_id' => $userId,
            'note' => $note,
        ]);
    }

    /**
     * Get audit history for a product.
     */
    public function getHistory(Product $product, int $limit = 50)
    {
        return InventoryMovement::query()
            ->where('product_id', $product->id)
            ->with(['user', 'product'])
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }
}
