<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'shift_id', 'user_id', 'type', 'amount', 'category', 'description', 'reference_type', 'reference_id',
    ];

    protected $casts = ['amount' => 'decimal:2'];

    public function shift(): BelongsTo { return $this->belongsTo(Shift::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function reference(): ?Model
    {
        if (! $this->reference_type || ! $this->reference_id) return null;
        return $this->reference_type::find($this->reference_id);
    }

    protected static function booted(): void
    {
        static::updating(fn (): bool => throw new \LogicException('Cash movements are append-only.'));
        static::deleting(fn (): bool => throw new \LogicException('Cash movements are append-only.'));
    }
}
