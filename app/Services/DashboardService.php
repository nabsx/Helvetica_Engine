<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Expense;
use App\Models\Order;
use App\Models\Product;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DashboardService
{
    public const OPERATIONAL_TIMEZONE = 'Asia/Jakarta';

    public function snapshot(string $date): array
    {
        $localDate = Carbon::createFromFormat('Y-m-d', $date, self::OPERATIONAL_TIMEZONE);
        $start = $localDate->copy()->startOfDay()->utc();
        $end = $localDate->copy()->endOfDay()->utc();
        $orders = Order::query()->paid()->forJakartaDate($date);
        $orderCount = (clone $orders)->count();
        $revenue = round((float) (clone $orders)->sum('total_amount'), 2);
        $cash = round((float) (clone $orders)->where('payment_type', 'CASH')->sum('total_amount'), 2);
        $qris = round((float) (clone $orders)->where('payment_type', 'QRIS')->sum('total_amount'), 2);
        $aov = $orderCount > 0 ? round($revenue / $orderCount, 2) : null;

        $hppRows = (clone $orders)->with('items.product')->get()->flatMap->items;
        $cogsCents = 0;
        $resolvedHppRows = 0;
        $unresolvedHppRows = 0;
        foreach ($hppRows as $item) {
            $snapshotCost = (float) ($item->unit_cost ?? 0);
            $masterCost = (float) ($item->product?->cost_price ?? 0);
            $unitCost = $snapshotCost > 0 ? $snapshotCost : $masterCost;
            $lineCents = (int) round($unitCost * (int) $item->quantity * 100, 0, PHP_ROUND_HALF_UP);
            $cogsCents += $lineCents;
            $lineCents > 0 ? $resolvedHppRows++ : $unresolvedHppRows++;
        }
        $dppCents = (int) round($hppRows->sum(fn ($item) => $item->dppAmount()) * 100, 0, PHP_ROUND_HALF_UP);
        $taxCents = (int) round($hppRows->sum(fn ($item) => $item->taxAmount()) * 100, 0, PHP_ROUND_HALF_UP);
        $gross = ['value' => (float) (($dppCents - $cogsCents) / 100), 'status' => $unresolvedHppRows > 0 ? 'Sebagian HPP belum tersedia' : 'HPP tercakup'];
        $expenseValue = round((float) Expense::query()->forJakartaDate($date)->sum('amount'), 2);
        $expenses = ['value' => $expenseValue, 'status' => $expenseValue > 0 ? 'Biaya operasional hari ini' : 'Tidak ada expense tercatat'];
        $net = ['value' => (float) ($gross['value'] - $expenseValue), 'status' => 'DPP - HPP - Expenses'];

        $paymentBreakdown = collect(['CASH' => $cash, 'QRIS' => $qris])->map(function (float $amount) use ($revenue): array {
            return ['amount' => $amount, 'percent' => $revenue > 0 ? round($amount / $revenue * 100) : 0];
        });

        $activeShifts = Shift::query()->whereIn('status', ['open', 'pending_close'])
            ->with('user')->latest('start_time')->get();
        $drawer = app(CashDrawerService::class);
        $cashMonitoring = $activeShifts->map(fn (Shift $shift): array => [
            'shift' => $shift,
            'expected' => $drawer->expected($shift),
            'cash_sales' => $drawer->getCashSales($shift),
            'status' => $shift->status === 'pending_close' ? 'Pending review' : 'Open',
        ]);

        $topProducts = Product::query()->select('products.id', 'products.name')
            ->join('order_items', 'products.id', '=', 'order_items.product_id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', 'paid')->whereBetween('orders.created_at', [$start, $end])
            ->groupBy('products.id', 'products.name')->selectRaw('SUM(order_items.quantity) AS quantity')
            ->orderByDesc('quantity')->limit(5)->get();

        $recentOrders = (clone $orders)->with('user')->latest()->limit(6)->get();
        $activities = ActivityLog::query()->with('user')->latest()->limit(8)->get();
        $lowStock = Product::query()->whereColumn('stock', '<=', 'low_stock_threshold')
            ->with('category')->orderBy('stock')->limit(6)->get();

        $chart = collect(range(0, 6))->map(function (int $offset) use ($localDate): array {
            $day = $localDate->copy()->subDays(6 - $offset);
            $from = $day->copy()->startOfDay()->utc();
            $to = $day->copy()->endOfDay()->utc();
            return ['label' => $day->format('D'), 'amount' => round((float) Order::query()->paid()->whereBetween('created_at', [$from, $to])->sum('total_amount'), 2)];
        });

        return compact('localDate', 'orderCount', 'revenue', 'cash', 'qris', 'aov', 'paymentBreakdown', 'cashMonitoring', 'topProducts', 'recentOrders', 'activities', 'lowStock', 'chart', 'gross', 'net', 'expenses', 'resolvedHppRows', 'unresolvedHppRows') + [
            'dpp' => (float) ($dppCents / 100),
            'tax' => (float) ($taxCents / 100),
            'accountingNote' => $unresolvedHppRows > 0
                ? "Profit dihitung dari HPP yang tersedia; {$unresolvedHppRows} baris belum memiliki HPP. Net = DPP - HPP - expense Jakarta."
                : 'Profit dihitung dari snapshot HPP per item atau fallback harga modal produk. Net = DPP - HPP - expense Jakarta.',
        ];
    }
}
