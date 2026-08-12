<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->decimal('cost_price', 12, 2)->default(0)->after('price');
            $table->string('tax_name')->default('PB1')->after('cost_price');
            $table->string('tax_code')->default('PB1')->after('tax_name');
            $table->decimal('tax_rate', 5, 2)->default(10)->after('tax_code');
            $table->boolean('tax_included')->default(false)->after('tax_rate');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn(['cost_price', 'tax_name', 'tax_code', 'tax_rate', 'tax_included']);
        });
    }
};
