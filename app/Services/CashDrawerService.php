<?php

namespace App\Services;

use App\Models\CashMovement;
use App\Models\Shift;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CashDrawerService
{
    public function getCashSales(Shift $shift): float
    {
        return round((float) $shift->orders()->where('payment_type', 'CASH')->where('status', 'paid')->sum('total_amount'), 2);
    }

    public function getCashRefunds(Shift $shift): float
    {
        return round((float) $shift->cashMovements()->where('type', 'out')->where('category', 'refund')->sum('amount'), 2);
    }

    public function getCashIn(Shift $shift): float
    {
        return round((float) $shift->cashMovements()->where('type', 'in')->sum('amount'), 2);
    }

    public function getCashOut(Shift $shift): float
    {
        return round((float) $shift->cashMovements()->where('type', 'out')->sum('amount'), 2);
    }

    public function calculateExpectedCash(Shift $shift): float
    {
        return round((float) ($shift->opening_cash ?? $shift->initial_cash)
            + $this->getCashSales($shift)
            + $this->getCashIn($shift)
            - $this->getCashOut($shift), 2);
    }

    public function expected(Shift $shift): float
    {
        return $this->calculateExpectedCash($shift);
    }

    public function closeShift(Shift $shift, float $closingCash): Shift
    {
        return DB::transaction(function () use ($shift, $closingCash): Shift {
            $locked = Shift::query()->whereKey($shift->id)->lockForUpdate()->firstOrFail();
            if ($locked->status !== 'open') {
                throw ValidationException::withMessages(['shift' => 'Shift ini sudah tidak aktif.']);
            }

            $expected = $this->calculateExpectedCash($locked);
            $locked->forceFill([
                'end_time' => now(),
                'actual_cash' => $closingCash,
                'closing_cash' => $closingCash,
                'cash_difference' => round($closingCash - $expected, 2),
                'status' => 'pending_close',
            ])->save();

            return $locked->fresh();
        });
    }

    public function approveShift(Shift $shift, int $reviewerId): Shift
    {
        return DB::transaction(function () use ($shift, $reviewerId): Shift {
            $locked = Shift::query()->whereKey($shift->id)->lockForUpdate()->firstOrFail();
            if ($locked->status !== 'pending_close') {
                throw ValidationException::withMessages(['shift' => 'Shift ini belum menunggu approval atau sudah diproses.']);
            }

            $locked->forceFill([
                'status' => 'approved',
            ])->save();

            return $locked->fresh(['user', 'opener']);
        });
    }

    public function recordMovement(Shift $shift, int $userId, string $type, float $amount, string $category, ?string $description = null, ?string $referenceType = null, ?int $referenceId = null): CashMovement
    {
        if ($amount <= 0 || ! in_array($type, ['in', 'out'], true)) {
            throw ValidationException::withMessages(['amount' => 'Jumlah dan tipe kas tidak valid.']);
        }
        if ($shift->status !== 'open') {
            throw ValidationException::withMessages(['shift' => 'Cash movement hanya dapat dicatat pada shift aktif.']);
        }

        return $shift->cashMovements()->create([
            'user_id' => $userId,
            'type' => $type,
            'amount' => $amount,
            'category' => $category,
            'description' => $description,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
        ]);
    }
}
