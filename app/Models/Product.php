<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'price',
        'cost_price',
        'tax_name',
        'tax_code',
        'tax_rate',
        'tax_included',
        'image',
        'stock',
        'low_stock_threshold',
        'is_available',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'tax_rate' => 'decimal:4',
        'tax_included' => 'boolean',
        'stock' => 'integer',
        'low_stock_threshold' => 'integer',
        'is_available' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /** Only menu items currently sellable at the counter. */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_available', true)
            ->where('stock', '>', 0);
    }
}
