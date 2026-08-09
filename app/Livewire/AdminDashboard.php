<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\View\View;
use Livewire\Component;

class AdminDashboard extends Component
{
    public string $tanggal;

    public function mount(): void
    {
        $this->tanggal = now()->toDateString();
    }

    public function render(): View
    {
        $date = Carbon::parse($this->tanggal);
        $query = Order::query()->paid()->whereBetween('created_at', [$date->copy()->startOfDay(), $date->copy()->endOfDay()]);
        $summary = (clone $query)->selectRaw('COUNT(*) total_transaksi, COALESCE(SUM(subtotal),0) gross, COALESCE(SUM(tax_amount),0) pajak, COALESCE(SUM(subtotal / 1.10),0) dpp, COALESCE(SUM(rounding_adjustment),0) pembulatan')->first();
        $payments = (clone $query)->select('payment_type')->selectRaw('COUNT(*) jumlah, COALESCE(SUM(total_amount),0) total')->groupBy('payment_type')->pluck('total', 'payment_type');

        return view('livewire.admin-dashboard', [
            'summary' => $summary,
            'cash' => $payments->get('CASH', 0),
            'qris' => $payments->get('QRIS', 0),
            'lowStock' => Product::query()->whereColumn('stock', '<=', 'low_stock_threshold')->with('category')->orderBy('stock')->limit(5)->get(),
        ]);
    }
}
