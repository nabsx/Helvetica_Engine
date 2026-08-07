<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\ReceiptService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

class ReceiptController extends Controller
{
    public function show(Order $order, ReceiptService $receiptService): View
    {
        abort_unless($order->status === 'paid', 404);

        return view('receipts.thermal', [
            'receipt' => $receiptService->payload($order),
            'autoPrint' => request()->boolean('print', true),
        ]);
    }

    public function escpos(Order $order, ReceiptService $receiptService): Response
    {
        abort_unless($order->status === 'paid', 404);

        $receipt = $receiptService->payload($order);
        $bytes = $this->buildEscPos($receipt);

        return response($bytes, 200, [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="'.$order->order_number.'.bin"',
        ]);
    }

    private function buildEscPos(array $receipt): string
    {
        $order = $receipt['order'];
        $store = $receipt['store'];
        $line = str_repeat('-', 32)."\n";
        $text = "\x1B@";
        $text .= "\x1Ba\x01".$store['name']."\n";
        $text .= "\x1Ba\x00".$store['address']."\n";
        $text .= $store['phone'] ? $store['phone']."\n" : '';
        $text .= $line;
        $text .= $order['number']."\n".$order['date']."\n";
        $text .= $line;

        foreach ($order['items'] as $item) {
            $text .= $item['name']."\n";
            $text .= sprintf("%dx %-17s %10s\n", $item['quantity'], '', $this->rupiah($item['subtotal']));
        }

        $text .= $line;
        $text .= $this->row('Total Belanja', $order['subtotal']);
        $text .= $this->row('PB1 termasuk', $order['tax_amount']);
        $text .= $this->row('Pembulatan', $order['rounding_adjustment']);
        $text .= $this->row('TOTAL', $order['total_amount']);

        if ($order['cash_given'] !== null) {
            $text .= $this->row('Tunai', $order['cash_given']);
            $text .= $this->row('Kembalian', $order['change_amount'] ?? 0);
        }

        $text .= "\n\x1Ba\x01Terima kasih\n\n\n";
        $text .= "\x1DVA\x00"; // cut paper
        $text .= "\x1Bp\x00\x19\xFA"; // cash drawer pulse

        return $text;
    }

    private function row(string $label, float $amount): string
    {
        return sprintf("%-20s %11s\n", $label, $this->rupiah($amount));
    }

    private function rupiah(float $amount): string
    {
        return number_format($amount, 0, ',', '.');
    }
}
