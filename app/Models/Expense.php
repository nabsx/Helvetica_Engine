<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = ['expense_number', 'expense_date', 'category', 'description', 'amount', 'created_by'];

    protected $casts = ['expense_date' => 'date', 'amount' => 'decimal:2'];

    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function user(): BelongsTo { return $this->creator(); }

    public function scopeForJakartaDate(Builder $query, string $date): Builder
    {
        return $query->whereDate('expense_date', Carbon::createFromFormat('Y-m-d', $date, 'Asia/Jakarta'));
    }

    public static function nextNumber(string $date): string
    {
        $prefix = 'EXP-'.str_replace('-', '', $date).'-';
        $last = static::query()->where('expense_number', 'like', $prefix.'%')->orderByDesc('expense_number')->value('expense_number');
        $sequence = $last ? ((int) substr($last, -3)) + 1 : 1;
        return $prefix.str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
    }
}
