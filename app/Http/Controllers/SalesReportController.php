<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SalesReportController extends Controller
{
    public function index(Request $request): View
    {
        $tanggal = $request->string('tanggal')->toString() ?: now()->toDateString();

        try {
            $hari = CarbonImmutable::createFromFormat('Y-m-d', $tanggal);
        } catch (\Throwable) {
            $hari = CarbonImmutable::today();
            $tanggal = $hari->toDateString();
        }

        return view('admin.sales-report', [
            'laporan' => $this->getLaporanHarian($hari),
            'tanggal' => $tanggal,
        ]);
    }

    public function getLaporanHarian(CarbonImmutable|string $tanggal): array
    {
        $hari = is_string($tanggal)
            ? CarbonImmutable::createFromFormat('Y-m-d', $tanggal)
            : $tanggal;

        $orders = Order::query()
            ->paid()
            ->whereBetween('created_at', [$hari->startOfDay(), $hari->endOfDay()]);

        $summary = (clone $orders)
            ->selectRaw('COUNT(*) AS total_transaksi')
            ->selectRaw('COALESCE(SUM(subtotal), 0) AS total_pendapatan_kotor')
            ->selectRaw('COALESCE(SUM(tax_amount), 0) AS total_pajak')
            ->selectRaw('COALESCE(SUM(subtotal / 1.10), 0) AS total_pendapatan_bersih')
            ->selectRaw('COALESCE(SUM(CASE WHEN payment_type = \'CASH\' THEN rounding_adjustment ELSE 0 END), 0) AS total_uang_pembulatan')
            ->first();

        $payments = (clone $orders)
            ->select('payment_type')
            ->selectRaw('COUNT(*) AS jumlah_transaksi')
            ->selectRaw('COALESCE(SUM(subtotal), 0) AS total_pendapatan')
            ->selectRaw('COALESCE(SUM(total_amount), 0) AS total_dibayar')
            ->groupBy('payment_type')
            ->get()
            ->keyBy('payment_type');

        return [
            'tanggal' => $hari->toDateString(),
            'total_transaksi' => (int) $summary->total_transaksi,
            'total_pendapatan_kotor' => (float) $summary->total_pendapatan_kotor,
            'total_pajak' => (float) $summary->total_pajak,
            'total_pendapatan_bersih' => (float) $summary->total_pendapatan_bersih,
            'total_uang_pembulatan' => (float) $summary->total_uang_pembulatan,
            'breakdown_pembayaran' => [
                'CASH' => $this->paymentSummary($payments->get('CASH')),
                'QRIS' => $this->paymentSummary($payments->get('QRIS')),
            ],
        ];
    }

    private function paymentSummary(?object $payment): array
    {
        return [
            'jumlah_transaksi' => (int) ($payment->jumlah_transaksi ?? 0),
            'total_pendapatan' => (float) ($payment->total_pendapatan ?? 0),
            'total_dibayar' => (float) ($payment->total_dibayar ?? 0),
        ];
    }
}
