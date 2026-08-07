<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->decimal('cash_given', 12, 2)->nullable()->after('payment_type');
            $table->decimal('change_amount', 12, 2)->nullable()->after('cash_given');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn(['cash_given', 'change_amount']);
        });
    }
};
