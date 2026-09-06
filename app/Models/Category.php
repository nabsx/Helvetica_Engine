<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'position',
        'is_active',
    ];

    protected $casts = [
        'position' => 'integer',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        // Auto-generate the slug from the name if the caller didn't supply one.
        static::creating(function (Category $category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }

            // New categories are appended to the end of the POS tab order.
            if (! isset($category->attributes['position'])) {
                $category->position = ((int) static::max('position')) + 1;
            }
        });
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /** Default display order everywhere: POS tab order, then id as a tiebreaker. */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('position')->orderBy('id');
    }

    /**
     * Purely cosmetic: picks a badge colour + icon for the admin UI based on
     * the category name (falling back to a colour rotation by id). No new
     * data is stored — this is derived at render time from the real name.
     */
    public function badgeTheme(): array
    {
        $palette = [
            ['bg' => 'bg-blue-50', 'text' => 'text-blue-600', 'border' => 'border-blue-100', 'icon' => 'cup'],
            ['bg' => 'bg-amber-50', 'text' => 'text-amber-600', 'border' => 'border-amber-100', 'icon' => 'coffee'],
            ['bg' => 'bg-purple-50', 'text' => 'text-purple-600', 'border' => 'border-purple-100', 'icon' => 'flask'],
            ['bg' => 'bg-orange-50', 'text' => 'text-orange-600', 'border' => 'border-orange-100', 'icon' => 'pastry'],
            ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'border' => 'border-emerald-100', 'icon' => 'tag'],
        ];

        $name = Str::lower($this->name);

        $icon = match (true) {
            Str::contains($name, ['beverage', 'minuman', 'jus', 'juice']) => 'cup',
            Str::contains($name, ['coffee', 'kopi']) => 'coffee',
            Str::contains($name, ['non-coffee', 'non coffee', 'tea', 'teh']) => 'flask',
            Str::contains($name, ['pastry', 'roti', 'kue', 'bakery', 'cake']) => 'pastry',
            default => null,
        };

        if ($icon) {
            return collect($palette)->firstWhere('icon', $icon) ?? $palette[$this->id % count($palette)];
        }

        return $palette[$this->id % count($palette)];
    }
}