<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->enum('type', ['sale', 'refund', 'adjustment', 'opname', 'initial']);
            $table->integer('quantity_delta'); // negatif untuk sale, positif untuk refund/adjustment/opname
            $table->unsignedInteger('stock_before');
            $table->unsignedInteger('stock_after');
            $table->nullable()->string('reference_type');
            $table->nullable()->unsignedBigInteger('reference_id');
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->nullable()->string('note');
            $table->timestamp('created_at')->useCurrent();

            // Index untuk query riwayat per produk
            $table->index(['product_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
