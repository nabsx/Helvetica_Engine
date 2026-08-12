<?php

namespace App\Services;

use App\Models\ActivityLog;
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
        $orders = Order::query()->paid()->whereBetween('created_at', [$start, $end]);
        $orderCount = (clone $orders)->count();
        $revenue = round((float) (clone $orders)->sum('total_amount'), 2);
        $cash = round((float) (clone $orders)->where('payment_type', 'CASH')->sum('total_amount'), 2);
        $qris = round((float) (clone $orders)->where('payment_type', 'QRIS')->sum('total_amount'), 2);
        $aov = $orderCount > 0 ? round($revenue / $orderCount, 2) : null;

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

        return compact('localDate', 'orderCount', 'revenue', 'cash', 'qris', 'aov', 'paymentBreakdown', 'cashMonitoring', 'topProducts', 'recentOrders', 'activities', 'lowStock', 'chart') + [
            'gross' => ['value' => null, 'status' => 'HPP belum dikonfigurasi'],
            'net' => ['value' => null, 'status' => 'Expense belum tercatat'],
            'accountingNote' => 'Gross Profit dan Net Profit belum tersedia karena HPP per item dan expense belum dicatat. Dashboard tidak menggunakan harga jual sebagai HPP.',
        ];
    }
}
