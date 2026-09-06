<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->unsignedInteger('position')->default(0)->after('slug');
            $table->boolean('is_active')->default(true)->after('position');
        });

        // Backfill: give existing categories a stable display order (by
        // current name ordering, since that was the previous default sort)
        // instead of leaving every row at position 0.
        DB::table('categories')->orderBy('name')->get(['id'])->each(function ($category, $index): void {
            DB::table('categories')->where('id', $category->id)->update(['position' => $index]);
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['position', 'is_active']);
        });
    }
};