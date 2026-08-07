<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * NOTE: This REPLACES Laravel's default users migration.
     * Delete/adjust the stock 0001_01_01_000000_create_users_table.php
     * that ships with a fresh install so you don't get a duplicate table
     * (keep the password_reset_tokens / sessions tables from that file if
     * you still need them — this app doesn't use email/password login).
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('pin'); // stored hashed, see User::setPinAttribute()
            $table->enum('role', ['admin', 'cashier', 'barista']);
            $table->rememberToken();
            $table->timestamps();

            $table->index('role');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
