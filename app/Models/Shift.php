<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'start_time',
        'end_time',
        'initial_cash',
        'actual_cash',
        'status',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'initial_cash' => 'decimal:2',
        'actual_cash' => 'decimal:2',
    ];

    // Auto-include the computed reconciliation fields when serialized to JSON.
    protected $appends = [
        'expected_cash',
        'variance',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', 'open');
    }

    /**
     * System-expected cash in the drawer: opening float + every CASH sale
     * booked during this shift. QRIS money never touches the physical
     * drawer, so it's deliberately excluded.
     */
    public function getExpectedCashAttribute(): float
    {
        $cashSales = $this->orders()
            ->where('payment_type', 'CASH')
            ->where('status', 'paid')
            ->sum('total_amount');

        return round((float) $this->initial_cash + (float) $cashSales, 2);
    }

    /**
     * What the cashier physically counted minus what the system expected.
     * Positive = surplus, negative = shortage. Null until the shift closes.
     */
    public function getVarianceAttribute(): ?float
    {
        if (is_null($this->actual_cash)) {
            return null;
        }

        return round((float) $this->actual_cash - $this->expected_cash, 2);
    }
}
