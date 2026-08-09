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
        'opened_by',
        'start_time',
        'end_time',
        'initial_cash',
        'opening_cash',
        'actual_cash',
        'closing_cash',
        'cash_difference',
        'status',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'initial_cash' => 'decimal:2',
        'opening_cash' => 'decimal:2',
        'actual_cash' => 'decimal:2',
        'closing_cash' => 'decimal:2',
        'cash_difference' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function cashMovements(): HasMany
    {
        return $this->hasMany(CashMovement::class);
    }

    public function opener(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', 'open');
    }
}
