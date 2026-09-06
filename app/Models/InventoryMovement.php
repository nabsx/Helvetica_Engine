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
        'user_id',
        'type',
        'quantity_delta',
        'stock_before',
        'stock_after',
        'reference_type',
        'reference_id',
        'note',
    ];

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

    /** Mirrors CashMovement::reference() — resolves whatever caused this movement (an Order, etc). */
    public function reference(): ?Model
    {
        if (! $this->reference_type || ! $this->reference_id) {
            return null;
        }

        return $this->reference_type::find($this->reference_id);
    }

    /**
     * Append-only, exactly like CashMovement: a ledger you can correct with
     * a new row, never by rewriting or deleting an old one.
     */
    protected static function booted(): void
    {
        static::updating(fn (): bool => throw new \LogicException('Inventory movements are append-only.'));
        static::deleting(fn (): bool => throw new \LogicException('Inventory movements are append-only.'));
    }
}
