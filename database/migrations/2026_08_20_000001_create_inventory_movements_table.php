<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_movements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->enum('type', ['sale', 'refund', 'adjustment', 'opname', 'initial']);
            // Positive for stock in (refund/adjustment up/initial), negative for
            // stock out (sale/adjustment down). Never zero — a movement that
            // changes nothing isn't a movement.
            $table->integer('quantity_delta');
            // Self-contained snapshot: every row can be audited on its own
            // without replaying the whole history from row 1.
            $table->unsignedInteger('stock_before');
            $table->unsignedInteger('stock_after');
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('note')->nullable();
            $table->timestamp('created_at')->useCurrent();

            // Append-only ledger: no updated_at, enforced further at the
            // model level (see InventoryMovement::booted()).

            // Product's stock history, newest first — the main read pattern
            // for both the admin UI and reconciliation/audit queries.
            $table->index(['product_id', 'created_at']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
