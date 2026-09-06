<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Existing stock history cannot be reconstructed reliably from old orders.
        // One initial baseline per product closes the audit gap without inventing events.
        if (! Schema::hasTable('inventory_movements')) {
            return;
        }

        DB::table('products')->orderBy('id')->each(function (object $product): void {
            if (DB::table('inventory_movements')->where('product_id', $product->id)->exists()) {
                return;
            }

            $userId = DB::table('users')->orderBy('id')->value('id');
            if (! $userId) {
                return;
            }

            DB::table('inventory_movements')->insert([
                'product_id' => $product->id,
                'type' => 'initial',
                'quantity_delta' => (int) $product->stock,
                'stock_before' => 0,
                'stock_after' => (int) $product->stock,
                'user_id' => $userId,
                'note' => 'Baseline stok saat inventory ledger diaktifkan.',
                'created_at' => now(),
            ]);
        });
    }

    public function down(): void
    {
        DB::table('inventory_movements')->where('type', 'initial')->delete();
    }
};
