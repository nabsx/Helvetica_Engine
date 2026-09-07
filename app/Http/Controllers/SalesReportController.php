<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Order;
use App\Models\Shift;
use App\Models\User;
use App\Services\DashboardService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class SalesReportController extends Controller
{
    private const TIMEZONE = DashboardService::OPERATIONAL_TIMEZONE;

    public function index(Request $request): View
    {
        $periode = in_array($request->string('periode')->toString(), ['harian', 'bulanan', 'tahunan'], true)
            ? $request->string('periode')->toString()
            : 'harian';
        $today = now(self::TIMEZONE);
        $tanggal = $this->parseDate($request->string('tanggal')->toString(), $today->toDateString());
        $bulan = $this->parseDate($request->string('bulan')->toString(), $today->startOfMonth()->toDateString(), 'Y-m-d', 'Y-m');
        $tahun = $this->parseDate($request->string('tahun')->toString(), $today->startOfYear()->toDateString(), 'Y-m-d', 'Y');
        $kasirId = $request->filled('kasir') ? $request->integer('kasir') : null;
        $shiftId = $periode === 'harian' && $request->filled('shift') ? $request->integer('shift') : null;

        [$start, $end, $periodValue] = match ($periode) {
            'bulanan' => [CarbonImmutable::parse($bulan, self::TIMEZONE)->startOfMonth(), CarbonImmutable::parse($bulan, self::TIMEZONE)->endOfMonth(), substr($bulan, 0, 7)],
            'tahunan' => [CarbonImmutable::parse($tahun, self::TIMEZONE)->startOfYear(), CarbonImmutable::parse($tahun, self::TIMEZONE)->endOfYear(), substr($tahun, 0, 4)],
            default => [CarbonImmutable::parse($tanggal, self::TIMEZONE)->startOfDay(), CarbonImmutable::parse($tanggal, self::TIMEZONE)->endOfDay(), $tanggal],
        };

        $laporan = $this->buildReport($start, $end, $periode, $kasirId, $shiftId);
        $kasirOptions = User::orderBy('name')->get(['id', 'name']);
        $shiftOptions = $periode === 'harian'
            ? Shift::whereBetween('start_time', [$start, $end])->with('user:id,name')->orderBy('start_time')->get()
            : collect();

        return view('admin.sales-report', compact('laporan', 'periode', 'tanggal', 'bulan', 'tahun', 'periodValue', 'kasirId', 'shiftId', 'kasirOptions', 'shiftOptions'));
    }

    public function getLaporanHarian(CarbonImmutable|string $tanggal): array
    {
        $day = is_string($tanggal) ? CarbonImmutable::createFromFormat('Y-m-d', $tanggal, self::TIMEZONE) : $tanggal->setTimezone(self::TIMEZONE);
        return $this->buildReport($day->startOfDay(), $day->endOfDay(), 'harian');
    }

    private function buildReport(CarbonImmutable $start, CarbonImmutable $end, string $periode, ?int $kasirId = null, ?int $shiftId = null): array
    {
        $orders = Order::query()->paid()
            ->whereBetween('created_at', [$start, $end])
            ->when($kasirId, fn ($q) => $q->where('user_id', $kasirId))
            ->when($shiftId, fn ($q) => $q->where('shift_id', $shiftId))
            ->with(['items.product', 'user:id,name'])->get();
        $items = $orders->flatMap->items;
        $dppCents = (int) round($items->sum(fn ($item) => $item->dppAmount()) * 100, 0, PHP_ROUND_HALF_UP);
        $taxCents = (int) round($orders->sum(fn ($order) => $this->orderTaxAmount($order)) * 100, 0, PHP_ROUND_HALF_UP);
        $cogs = (float) $items->sum(fn ($item) => (float) ($item->unit_cost ?? $item->product?->cost_price ?? 0) * (int) $item->quantity);
        $expense = (float) Expense::query()->whereBetween('expense_date', [$start->toDateString(), $end->toDateString()])->sum('amount');
        $grossProfit = ($dppCents / 100) - $cogs;
        $payments = $orders->groupBy('payment_type');
        $totalKotor = (float) $orders->sum('total_amount');

        return [
            'periode' => $periode, 'tanggal' => $start->toDateString(),
            'total_transaksi' => $orders->count(), 'total_pendapatan_kotor' => $totalKotor,
            'total_pendapatan' => $totalKotor, 'total_pajak' => $taxCents / 100,
            'total_pendapatan_bersih' => $dppCents / 100, 'total_expense' => $expense,
            'gross_profit' => $grossProfit, 'net_profit' => $grossProfit - $expense,
            'total_uang_pembulatan' => (float) $orders->where('payment_type', 'CASH')->sum('rounding_adjustment'),
            'tarif_pajak_efektif' => $dppCents > 0 ? ($taxCents / $dppCents) * 100 : 0.0,
            'transaksi' => $orders,
            'breakdown_pembayaran' => ['CASH' => $this->paymentSummaryFromGroup($payments->get('CASH')), 'QRIS' => $this->paymentSummaryFromGroup($payments->get('QRIS'))],
            'trend' => $this->trend($orders, $start, $end, $periode),
        ];
    }

    private function orderTaxAmount(Order $order): float
    {
        $storedTax = (float) ($order->total_tax ?? $order->tax_amount ?? 0);

        if ($storedTax > 0) {
            return $storedTax;
        }

        return (float) $order->items->sum(fn ($item) => $item->taxAmount());
    }

    private function trend(Collection $orders, CarbonImmutable $start, CarbonImmutable $end, string $periode): array
    {
        $keys = $periode === 'tahunan'
            ? collect(range(1, 12))->map(fn ($month) => $start->setMonth($month)->format('Y-m'))
            : ($periode === 'bulanan' ? collect(range(0, $start->daysInMonth - 1))->map(fn ($day) => $start->addDays($day)->toDateString()) : collect([$start->toDateString()]));
        $grouped = $orders->groupBy(fn ($order) => $order->created_at?->timezone(self::TIMEZONE)->format($periode === 'tahunan' ? 'Y-m' : 'Y-m-d'));
        return $keys->map(fn ($key) => ['label' => $periode === 'tahunan' ? CarbonImmutable::createFromFormat('Y-m', $key, self::TIMEZONE)->translatedFormat('M') : CarbonImmutable::parse($key, self::TIMEZONE)->format('d'), 'total_pendapatan' => (float) ($grouped->get($key)?->sum('total_amount') ?? 0), 'total_transaksi' => $grouped->get($key)?->count() ?? 0])->values()->all();
    }

    private function parseDate(string $value, string $fallback, string $format = 'Y-m-d', string $inputFormat = 'Y-m-d'): string
    {
        try { return CarbonImmutable::createFromFormat($inputFormat, $value, self::TIMEZONE)->format($format); } catch (\Throwable) { return $fallback; }
    }

    private function paymentSummaryFromGroup(?Collection $orders): array
    {
        $totalDibayar = (float) ($orders?->sum('total_amount') ?? 0);
        $totalFee = (float) ($orders?->sum('gateway_fee_amount') ?? 0);

        return [
            'jumlah_transaksi' => $orders?->count() ?? 0,
            'total_pendapatan' => (float) ($orders?->sum('subtotal') ?? 0),
            'total_dibayar' => $totalDibayar,
            'total_fee' => $totalFee,
            'total_bersih' => $totalDibayar - $totalFee,
        ];
    }
}
