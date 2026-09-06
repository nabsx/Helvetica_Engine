<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'product_id',
        'type',
        'quantity_delta',
        'stock_before',
        'stock_after',
        'reference_type',
        'reference_id',
        'user_id',
        'note',
    ];

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'sale' => 'Penjualan',
            'refund' => 'Refund',
            'adjustment' => 'Adjustment',
            'opname' => 'Stock opname',
            'initial' => 'Saldo awal',
            default => ucfirst((string) $this->type),
        };
    }

    protected $casts = [
        'quantity_delta' => 'integer',
        'stock_before' => 'integer',
        'stock_after' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reference(): ?Model
    {
        if (! $this->reference_type || ! $this->reference_id) {
            return null;
        }

        return $this->reference_type::find($this->reference_id);
    }

    protected static function booted(): void
    {
        static::updating(fn (): bool => throw new \LogicException('Inventory movements are append-only.'));
        static::deleting(fn (): bool => throw new \LogicException('Inventory movements are append-only.'));
    }
}
