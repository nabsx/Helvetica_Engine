<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $date = Carbon::today();
        $query = Order::query()->paid()->whereBetween('created_at', [$date->copy()->startOfDay(), $date->copy()->endOfDay()]);
        $summary = (clone $query)->selectRaw('COUNT(*) AS total_transaksi, COALESCE(SUM(subtotal), 0) AS gross, COALESCE(SUM(tax_amount), 0) AS pajak, COALESCE(SUM(subtotal / 1.10), 0) AS dpp, COALESCE(SUM(rounding_adjustment), 0) AS pembulatan')->first();
        $payments = (clone $query)->select('payment_type')->selectRaw('COALESCE(SUM(total_amount), 0) AS total')->groupBy('payment_type')->pluck('total', 'payment_type');

        return view('admin.dashboard', [
            'summary' => $summary,
            'cash' => $payments->get('CASH', 0),
            'qris' => $payments->get('QRIS', 0),
            'lowStock' => Product::query()->whereColumn('stock', '<=', 'low_stock_threshold')->with('category')->orderBy('stock')->limit(5)->get(),
        ]);
    }
}
