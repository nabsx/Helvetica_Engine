<?php

namespace App\Http\Controllers;

use App\Models\Expense;
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
            ? CarbonImmutable::createFromFormat('Y-m-d', $tanggal, 'Asia/Jakarta')
            : $tanggal->setTimezone('Asia/Jakarta');
        $start = $hari->startOfDay()->utc();
        $end = $hari->endOfDay()->utc();

        $orders = Order::query()
            ->paid()
            ->whereBetween('created_at', [$start, $end])
            ->with('items.product');
        $orderRows = $orders->get();
        $taxTotals = [];
        $dppCents = 0;

        foreach ($orderRows as $order) {
            foreach ($order->items as $item) {
                $grossCents = (int) round((float) $item->subtotal * 100, 0, PHP_ROUND_HALF_UP);
                $rate = (float) $item->tax_rate;
                if ($item->tax_included && $rate > 0) {
                    $rateBasisPoints = (int) round($rate * 100);
                    $lineDpp = (int) round($grossCents * 10000 / (10000 + $rateBasisPoints), 0, PHP_ROUND_HALF_UP);
                    $lineTax = $grossCents - $lineDpp;
                    $name = $item->tax_name ?: ($item->tax_code ?: 'Pajak');
                    $rateLabel = rtrim(rtrim(number_format($rate, 2, '.', ''), '0'), '.');
                    $key = $name.'|'.$rateLabel;
                    $taxTotals[$key] = ($taxTotals[$key] ?? 0) + $lineTax;
                    $dppCents += $lineDpp;
                } else {
                    $dpp = $item->getAttribute('dpp_amount');
                    $dppCents += is_numeric($dpp)
                        ? (int) round((float) $dpp * 100, 0, PHP_ROUND_HALF_UP)
                        : (int) round(((float) $item->subtotal / 1.10) * 100, 0, PHP_ROUND_HALF_UP);
                }
            }
        }

        $payments = $orderRows->groupBy('payment_type');
        $totalExpense = (float) Expense::query()->forJakartaDate($hari->toDateString())->sum('amount');
        $cogs = $orderRows->flatMap->items->sum(fn ($item) => (float) ($item->unit_cost ?? $item->product?->cost_price ?? 0) * (int) $item->quantity);
        $grossProfit = ($dppCents / 100) - $cogs;

        return [
            'tanggal' => $hari->toDateString(),
            'total_transaksi' => $orderRows->count(),
            'total_pendapatan_kotor' => (float) $orderRows->sum('total_amount'),
            'total_pendapatan' => (float) $orderRows->sum('total_amount'),
            'total_pajak' => array_sum($taxTotals) / 100,
            'total_pendapatan_bersih' => $dppCents / 100,
            'total_expense' => $totalExpense,
            'gross_profit' => $grossProfit,
            'net_profit' => $grossProfit - $totalExpense,
            'total_uang_pembulatan' => (float) $orderRows->where('payment_type', 'CASH')->sum('rounding_adjustment'),
            'pajak_terkumpul' => collect($taxTotals)->map(fn ($amount, $key) => [
                'label' => 'Termasuk '.str_replace('|', ' ', $key).'%',
                'amount' => $amount / 100,
            ])->values()->all(),
            'transaksi' => $orderRows,
            'breakdown_pembayaran' => [
                'CASH' => $this->paymentSummaryFromGroup($payments->get('CASH')),
                'QRIS' => $this->paymentSummaryFromGroup($payments->get('QRIS')),
            ],
        ];
    }

    private function paymentSummaryFromGroup($orders): array
    {
        return [
            'jumlah_transaksi' => $orders?->count() ?? 0,
            'total_pendapatan' => (float) ($orders?->sum('subtotal') ?? 0),
            'total_dibayar' => (float) ($orders?->sum('total_amount') ?? 0),
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
