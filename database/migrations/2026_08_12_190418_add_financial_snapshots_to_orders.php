<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->decimal('gateway_fee_rate', 7, 4)->default(0)->after('pg_fee');
            $table->decimal('gateway_fee_flat', 12, 2)->default(0)->after('gateway_fee_rate');
            $table->decimal('gateway_fee_amount', 12, 2)->default(0)->after('gateway_fee_flat');
            $table->string('gateway_fee_mode')->default('none')->after('gateway_fee_amount');
            $table->string('gateway_fee_basis')->default('none')->after('gateway_fee_mode');
            $table->decimal('total_tax', 12, 2)->default(0)->after('tax_amount');
        });

        Schema::table('order_items', function (Blueprint $table): void {
            $table->decimal('unit_cost', 12, 2)->default(0)->after('price');
            $table->string('product_name')->nullable()->after('product_id');
            $table->string('tax_name')->nullable()->after('unit_cost');
            $table->string('tax_code')->nullable()->after('tax_name');
            $table->decimal('tax_rate', 7, 4)->default(0)->after('tax_code');
            $table->boolean('tax_included')->default(false)->after('tax_rate');
            $table->decimal('taxable_base', 12, 2)->default(0)->after('tax_included');
            $table->decimal('tax_amount', 12, 2)->default(0)->after('taxable_base');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table): void {
            $table->dropColumn(['unit_cost', 'product_name', 'tax_name', 'tax_code', 'tax_rate', 'tax_included', 'taxable_base', 'tax_amount']);
        });
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn(['gateway_fee_rate', 'gateway_fee_flat', 'gateway_fee_amount', 'gateway_fee_mode', 'gateway_fee_basis', 'total_tax']);
        });
    }
};
