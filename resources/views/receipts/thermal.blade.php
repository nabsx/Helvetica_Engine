<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $receipt['order']['number'] }}</title>
    <style>
        @page { size: 58mm auto; margin: 0; }
        * { box-sizing: border-box; }
        body { width: 58mm; margin: 0 auto; padding: 3mm; color: #111; background: #fff; font: 11px/1.35 "Courier New", monospace; }
        .center { text-align: center; }
        .row { display: flex; justify-content: space-between; gap: 8px; font-family: "JetBrains Mono", "Courier New", monospace; font-variant-numeric: tabular-nums; }
        .item-name { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .muted { color: #555; }
        hr { border: 0; border-top: 1px dashed #111; margin: 7px 0; }
        h1 { font-size: 14px; margin: 0; }
        .total { font-weight: 700; font-size: 13px; }
        @media screen { body { margin-top: 16px; box-shadow: 0 0 12px #bbb; } }
    </style>
</head>
<body>
    <header class="center">
        <h1>{{ $receipt['store']['name'] }}</h1>
        <div>{{ $receipt['store']['address'] }}</div>
        @if($receipt['store']['phone']) <div>{{ $receipt['store']['phone'] }}</div> @endif
    </header>

    <hr>
    <div>{{ $receipt['order']['number'] }}</div>
    <div class="muted">{{ $receipt['order']['date'] }} · {{ $receipt['order']['cashier'] }}</div>
    <div class="muted">Pembayaran: {{ $receipt['order']['payment_type'] }}</div>
    <hr>

    @foreach($receipt['order']['items'] as $item)
        <div class="item-name">{{ $item['name'] }}</div>
        <div class="row">
            <span>{{ $item['quantity'] }} x {{ number_format($item['price'], 0, ',', '.') }}</span>
            <span>{{ number_format($item['subtotal'], 0, ',', '.') }}</span>
        </div>
    @endforeach

    <hr>
    <div class="row"><span>Total Belanja</span><span>{{ number_format($receipt['order']['subtotal'], 0, ',', '.') }}</span></div>
    <div class="row"><span>DPP</span><span>{{ number_format($receipt['order']['dpp'], 0, ',', '.') }}</span></div>
    @foreach($receipt['order']['tax_summary'] as $tax)
        <div class="row"><span>{{ $tax['label'] }}</span><span>{{ $tax['tax_amount'] === null ? '-' : number_format($tax['tax_amount'], 0, ',', '.') }}</span></div>
    @endforeach
    <div class="row"><span>Pembulatan</span><span>{{ number_format($receipt['order']['rounding_adjustment'], 0, ',', '.') }}</span></div>
    <div class="row total"><span>TOTAL</span><span>{{ number_format($receipt['order']['total_amount'], 0, ',', '.') }}</span></div>

    @if($receipt['order']['cash_given'] !== null)
        <div class="row"><span>Tunai</span><span>{{ number_format($receipt['order']['cash_given'], 0, ',', '.') }}</span></div>
        <div class="row"><span>Kembalian</span><span>{{ number_format($receipt['order']['change_amount'] ?? 0, 0, ',', '.') }}</span></div>
    @endif

    <p class="center">Terima kasih</p>

    @if($autoPrint)
        <script>window.addEventListener('load', () => window.print());</script>
    @endif
</body>
</html>
