<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * We deliberately do NOT try to reconstruct every historical sale/refund
     * as a backdated inventory_movements row — that would require replaying
     * every order_item ever created and guessing timestamps/users for stock
     * edits that happened before this table existed, which is more likely
     * to fabricate a false audit trail than to produce a true one.
     *
     * Instead we record one 'initial' row per existing product using its
     * current stock as the baseline. From this migration forward, every
     * change is captured by InventoryService. This is the same trade-off
     * already made for `cash_movements` (which also starts counting from
     * the day it was introduced, not from the shop's first ever sale).
     */
    public function up(): void
    {
        $systemUserId = DB::table('users')->where('role', 'admin')->value('id')
            ?? DB::table('users')->orderBy('id')->value('id');

        if ($systemUserId === null) {
            // No users table populated yet (fresh install) — nothing to backfill.
            return;
        }

        $now = now();

        DB::table('products')->select('id', 'stock')->orderBy('id')->chunkById(200, function ($products) use ($systemUserId, $now): void {
            $rows = $products->map(fn ($product) => [
                'product_id' => $product->id,
                'user_id' => $systemUserId,
                'type' => 'initial',
                'quantity_delta' => $product->stock,
                'stock_before' => 0,
                'stock_after' => $product->stock,
                'reference_type' => null,
                'reference_id' => null,
                'note' => 'Baseline recorded when inventory_movements was introduced.',
                'created_at' => $now,
            ])->all();

            if ($rows !== []) {
                DB::table('inventory_movements')->insert($rows);
            }
        });
    }

    public function down(): void
    {
        DB::table('inventory_movements')->where('type', 'initial')
            ->where('note', 'Baseline recorded when inventory_movements was introduced.')
            ->delete();
    }
};
