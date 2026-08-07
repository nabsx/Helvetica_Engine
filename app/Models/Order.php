<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'user_id',
        'shift_id',
        'subtotal',
        'tax_amount',
        'total_amount',
        'payment_type',
        'rounding_adjustment',
        'cash_given',
        'change_amount',
        'pg_fee',
        'net_received',
        'status',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'rounding_adjustment' => 'decimal:2',
        'cash_given' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'pg_fee' => 'decimal:2',
        'net_received' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', 'paid');
    }

    /**
     * Human-readable invoice number built from the row's own auto-increment
     * id, e.g. HLV-20260804-0007. Called AFTER the row is inserted, so it
     * never races with a concurrent insert (unlike a "count existing rows"
     * approach, which can produce duplicate numbers under load).
     */
    public static function formatOrderNumber(int $id): string
    {
        return sprintf('HLV-%s-%04d', now()->format('Ymd'), $id);
    }
}
