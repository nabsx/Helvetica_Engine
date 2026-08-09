<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shifts', function (Blueprint $table): void {
            $table->decimal('opening_cash', 12, 2)->default(0)->after('initial_cash');
            $table->decimal('closing_cash', 12, 2)->nullable()->after('actual_cash');
            $table->decimal('cash_difference', 12, 2)->nullable()->after('closing_cash');
            $table->foreignId('opened_by')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
        });

        Schema::table('shifts', function (Blueprint $table): void {
            $table->index(['user_id', 'status', 'start_time']);
        });

        Schema::create('cash_movements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shift_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->enum('type', ['in', 'out']);
            $table->decimal('amount', 12, 2)->unsigned();
            $table->string('category');
            $table->string('description')->nullable();
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->timestamps();
            $table->index(['shift_id', 'created_at']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_movements');

        Schema::table('shifts', function (Blueprint $table): void {
            $table->dropForeign(['opened_by']);
            $table->dropIndex(['user_id', 'status', 'start_time']);
            $table->dropColumn(['opening_cash', 'closing_cash', 'cash_difference', 'opened_by']);
        });
    }
};
