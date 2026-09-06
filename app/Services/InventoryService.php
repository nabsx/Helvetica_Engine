<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\Product;
use Illuminate\Validation\ValidationException;

/**
 * The only place that is allowed to change `products.stock`. Every method
 * here both updates the product's stock and writes the matching
 * InventoryMovement row in one call, so the two can never drift apart.
 *
 * IMPORTANT: this service does not open its own DB transaction and does not
 * lock the product itself. Callers (OrderController, AdminCancellationController,
 * ...) already run inside `DB::transaction(...)` and already hold the row
 * lock via `lockForUpdate()` on the $product they pass in — re-locking here
 * would be redundant and, worse, could mask a caller that forgot to lock at
 * all. Always pass in a $product instance that was fetched with
 * `lockForUpdate()` inside an active transaction.
 */
class InventoryService
{
    public function recordSale(Product $product, int $quantity, int $userId, ?string $referenceType = null, ?int $referenceId = null): InventoryMovement
    {
        $this->assertPositiveQuantity($quantity);

        if ($product->stock < $quantity) {
            throw ValidationException::withMessages([
                'stock' => "Stok {$product->name} tidak mencukupi.",
            ]);
        }

        return $this->apply($product, -$quantity, 'sale', $userId, $referenceType, $referenceId);
    }

    public function recordRefund(Product $product, int $quantity, int $userId, ?string $referenceType = null, ?int $referenceId = null): InventoryMovement
    {
        $this->assertPositiveQuantity($quantity);

        return $this->apply($product, $quantity, 'refund', $userId, $referenceType, $referenceId);
    }

    /**
     * Called once, right after a brand-new product is created with an
     * opening stock count. Unlike apply(), this does NOT touch
     * $product->stock — the caller already set it via Product::create() —
     * it only writes the matching ledger row so the new product's stock
     * doesn't silently start its life with zero audit trail.
     */
    public function recordInitial(Product $product, int $userId): ?InventoryMovement
    {
        if ($product->stock <= 0) {
            return null;
        }

        return InventoryMovement::create([
            'product_id' => $product->id,
            'user_id' => $userId,
            'type' => 'initial',
            'quantity_delta' => $product->stock,
            'stock_before' => 0,
            'stock_after' => $product->stock,
            'note' => 'Stok awal saat produk dibuat.',
        ]);
    }

    /**
     * Manual correction from the admin panel — e.g. after a physical stock
     * count (opname) or fixing a data-entry mistake. $newStock is the
     * counted/target value; the delta is derived from the product's current
     * stock, not typed in directly, so the ledger always reflects what
     * actually changed rather than what the admin thinks changed.
     */
    public function recordAdjustment(Product $product, int $newStock, string $note, int $userId): InventoryMovement
    {
        if ($newStock < 0) {
            throw ValidationException::withMessages([
                'new_stock' => 'Stok tidak boleh negatif.',
            ]);
        }

        $delta = $newStock - $product->stock;

        if ($delta === 0) {
            throw ValidationException::withMessages([
                'new_stock' => 'Stok baru sama dengan stok saat ini — tidak ada perubahan untuk dicatat.',
            ]);
        }

        return $this->apply($product, $delta, 'adjustment', $userId, note: $note);
    }

    private function apply(
        Product $product,
        int $delta,
        string $type,
        int $userId,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $note = null,
    ): InventoryMovement {
        $before = $product->stock;
        $after = $before + $delta;

        if ($after < 0) {
            // Defensive: callers should have already checked this (recordSale
            // does), but this guard stops any future caller from silently
            // pushing stock negative.
            throw ValidationException::withMessages([
                'stock' => "Perubahan stok ini akan membuat stok {$product->name} menjadi negatif.",
            ]);
        }

        $product->forceFill(['stock' => $after])->save();

        return InventoryMovement::create([
            'product_id' => $product->id,
            'user_id' => $userId,
            'type' => $type,
            'quantity_delta' => $delta,
            'stock_before' => $before,
            'stock_after' => $after,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'note' => $note,
        ]);
    }

    private function assertPositiveQuantity(int $quantity): void
    {
        if ($quantity <= 0) {
            throw ValidationException::withMessages([
                'quantity' => 'Jumlah harus lebih dari 0.',
            ]);
        }
    }
}
