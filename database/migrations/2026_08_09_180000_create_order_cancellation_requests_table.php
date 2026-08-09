<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A cashier can never cancel an order by themselves — this table is
     * the approval queue. An order's `status` only flips to 'cancelled'
     * once an admin approves the request (see AdminCancellationController).
     * Until then the order stays 'paid' and still counts toward the
     * cashier's expected_cash for shift reconciliation, which is exactly
     * what we want: money doesn't disappear from the drawer just because
     * someone typed a reason into a text box.
     */
    public function up(): void
    {
        Schema::create('order_cancellation_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->restrictOnDelete();
            $table->foreignId('requested_by')->constrained('users')->restrictOnDelete();
            $table->text('reason');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamps();

            // One order can only have one *pending* request at a time — enforced
            // in the controller (a partial unique index isn't portable across
            // MySQL/SQLite), but this index makes that check and the admin
            // queue listing fast.
            $table->index(['order_id', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_cancellation_requests');
    }
};
