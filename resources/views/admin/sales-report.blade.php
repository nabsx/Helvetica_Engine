<x-admin-layout title="Laporan Penjualan">
    @php
        $periodLabels = ['harian' => 'Harian', 'bulanan' => 'Bulanan', 'tahunan' => 'Tahunan'];
        $periodText = match ($periode) {
            'bulanan' => \Carbon\CarbonImmutable::parse($bulan)->translatedFormat('F Y'),
            'tahunan' => $tahun,
            default => \Carbon\CarbonImmutable::parse($tanggal)->translatedFormat('d F Y'),
        };
        $maxRevenue = max(1, collect($laporan['trend'])->max('total_pendapatan'));
        $hasTrend = collect($laporan['trend'])->sum('total_transaksi') > 0;
        $cards = [
            ['label' => 'Total transaksi', 'value' => number_format($laporan['total_transaksi'], 0, ',', '.')],
            ['label' => 'Pendapatan kotor', 'value' => 'Rp '.number_format($laporan['total_pendapatan_kotor'], 0, ',', '.')],
            ['label' => 'PB1 terkumpul', 'value' => 'Rp '.number_format($laporan['total_pajak'], 0, ',', '.')],
            ['label' => 'Pendapatan bersih / DPP', 'value' => 'Rp '.number_format($laporan['total_pendapatan_bersih'], 0, ',', '.')],
            ['label' => 'Pembulatan CASH', 'value' => 'Rp '.number_format($laporan['total_uang_pembulatan'], 0, ',', '.')],
            ['label' => 'Total expense', 'value' => 'Rp '.number_format($laporan['total_expense'], 0, ',', '.')],
            ['label' => 'Net profit', 'value' => 'Rp '.number_format($laporan['net_profit'], 0, ',', '.')],
        ];
    @endphp

    <div class="mb-7 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div><p class="text-sm font-semibold text-emerald-600">Analitik penjualan</p><h1 class="mt-1 text-3xl font-black tracking-tight">Laporan penjualan</h1><p class="mt-2 text-sm text-slate-500">Pantau performa transaksi berdasarkan periode dan metode pembayaran.</p></div>
        <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-right shadow-sm"><p class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400">Periode aktif</p><p class="mt-1 font-semibold text-slate-800">{{ $periodText }}</p></div>
    </div>

    <main class="space-y-6" x-data="{ periode: @js($periode) }">
        <form method="GET" action="{{ route('admin.sales-report') }}" class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="flex overflow-x-auto border-b border-slate-200 px-5 pt-2" role="tablist" aria-label="Jenis periode">
                @foreach ($periodLabels as $key => $label)
                    <button type="button" @click="periode = '{{ $key }}'" :class="periode === '{{ $key }}' ? 'border-emerald-600 text-emerald-700' : 'border-transparent text-slate-500 hover:text-slate-800'" class="-mb-px whitespace-nowrap border-b-2 px-4 py-3 text-sm font-bold">{{ $label }}</button>
                @endforeach
            </div>
            <input type="hidden" name="periode" :value="periode">
            <div class="flex flex-wrap items-end gap-3 p-5">
                <div x-show="periode === 'harian'"><label for="tanggal" class="mb-1 block text-sm font-medium text-slate-600">Tanggal laporan</label><input id="tanggal" name="tanggal" type="date" value="{{ $tanggal }}" class="rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"></div>
                <div x-show="periode === 'bulanan'"><label for="bulan" class="mb-1 block text-sm font-medium text-slate-600">Bulan laporan</label><input id="bulan" name="bulan" type="month" value="{{ $bulan }}" class="rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"></div>
                <div x-show="periode === 'tahunan'"><label for="tahun" class="mb-1 block text-sm font-medium text-slate-600">Tahun laporan</label><input id="tahun" name="tahun" type="number" min="2000" max="2100" value="{{ substr($tahun, 0, 4) }}" class="w-32 rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"></div>
                <button class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-500">Tampilkan laporan</button>
            </div>
        </form>

        @if ($periode !== 'harian')
            <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6" aria-labelledby="trend-title">
                <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between"><div><p class="text-xs font-bold uppercase tracking-[0.16em] text-emerald-600">Tren pendapatan</p><h2 id="trend-title" class="mt-1 text-lg font-bold">Pergerakan {{ strtolower($periodLabels[$periode]) }}</h2></div><p class="text-sm text-slate-500">{{ $hasTrend ? 'Pendapatan kotor · Rp' : 'Belum ada transaksi pada periode ini' }}</p></div>
                @if ($hasTrend)
                    <div class="mt-6 overflow-x-auto"><div class="min-w-[680px]"><div class="flex h-56 items-end gap-1 border-b border-l border-slate-200 px-2 pb-0 sm:gap-2">@foreach ($laporan['trend'] as $point)<div class="group flex h-full flex-1 flex-col justify-end"><div class="relative mx-auto w-full max-w-8 rounded-t-md bg-emerald-500 transition hover:bg-emerald-600" style="height: {{ max(3, ($point['total_pendapatan'] / $maxRevenue) * 100) }}%" title="{{ $point['label'] }}: Rp {{ number_format($point['total_pendapatan'], 0, ',', '.') }} · {{ $point['total_transaksi'] }} transaksi"><span class="pointer-events-none absolute bottom-full left-1/2 mb-2 hidden -translate-x-1/2 whitespace-nowrap rounded bg-slate-900 px-2 py-1 text-[10px] text-white group-hover:block">Rp {{ number_format($point['total_pendapatan'], 0, ',', '.') }}</span></div><span class="mt-2 truncate text-center text-[10px] text-slate-500">{{ $point['label'] }}</span></div>@endforeach</div></div></div>
                @else
                    <div class="mt-6 rounded-lg bg-slate-50 px-5 py-10 text-center"><p class="font-semibold text-slate-700">Belum ada data untuk grafik</p><p class="mt-1 text-sm text-slate-500">Pilih periode lain untuk melihat tren transaksi.</p></div>
                @endif
            </section>
        @endif

        <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7">@foreach ($cards as $card)<article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm text-slate-500">{{ $card['label'] }}</p><p class="mt-2 text-xl font-mono font-semibold tabular-nums text-slate-900">{{ $card['value'] }}</p></article>@endforeach</section>

        <section class="rounded-xl border border-slate-200 bg-white shadow-sm"><div class="border-b border-slate-200 px-5 py-4"><h2 class="font-bold">Pendapatan berdasarkan metode pembayaran</h2><p class="mt-1 text-sm text-slate-500">Periode {{ $periodText }}</p></div><div class="overflow-x-auto"><table class="min-w-full text-left text-sm"><thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-5 py-3">Metode</th><th class="px-5 py-3">Jumlah transaksi</th><th class="px-5 py-3">Pendapatan kotor</th><th class="px-5 py-3">Total dibayar</th></tr></thead><tbody class="divide-y divide-slate-100">@foreach (['CASH', 'QRIS'] as $method) @php $payment = $laporan['breakdown_pembayaran'][$method]; @endphp<tr><td class="px-5 py-4 font-semibold">{{ $method }}</td><td class="px-5 py-4">{{ number_format($payment['jumlah_transaksi'], 0, ',', '.') }}</td><td class="px-5 py-4">Rp {{ number_format($payment['total_pendapatan'], 0, ',', '.') }}</td><td class="px-5 py-4 font-semibold">Rp {{ number_format($payment['total_dibayar'], 0, ',', '.') }}</td></tr>@endforeach</tbody></table></div></section>

        <section class="rounded-xl border border-slate-200 bg-white shadow-sm"><div class="border-b border-slate-200 px-5 py-4"><h2 class="font-bold">Detail transaksi</h2><p class="mt-1 text-sm text-slate-500">Order paid pada periode {{ $periodText }}.</p></div><div class="divide-y divide-slate-100">@forelse ($laporan['transaksi'] as $order)<details class="group px-5 py-4"><summary class="flex cursor-pointer list-none items-center justify-between gap-4"><div><p class="font-semibold">{{ $order->order_number }}</p><p class="mt-1 text-xs text-slate-500">{{ strtoupper($order->payment_type) }} · {{ $order->created_at?->timezone('Asia/Jakarta')->format('d M Y, H:i') }}</p></div><div class="flex items-center gap-4"><span class="font-semibold">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span><span class="text-sm text-emerald-600 group-open:hidden">Lihat detail</span><span class="hidden text-sm text-slate-500 group-open:inline">Tutup</span></div></summary><div class="mt-4 overflow-x-auto rounded-lg bg-slate-50 p-4"><div class="mb-4 grid gap-3 border-b border-slate-200 pb-4 sm:grid-cols-2"><div><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Metode pembayaran</p><p class="mt-1 font-semibold text-slate-800">{{ strtoupper($order->payment_type) }}</p></div>@if (strtoupper($order->payment_type) === 'CASH')<div><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Uang pelanggan</p><p class="mt-1 font-semibold text-slate-800">Rp {{ number_format($order->cash_given ?? 0, 0, ',', '.') }}</p><p class="mt-1 text-xs text-slate-500">Kembalian: Rp {{ number_format($order->change_amount ?? 0, 0, ',', '.') }}</p></div>@else<div><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Status pembayaran</p><p class="mt-1 font-semibold text-emerald-700">Pembayaran {{ strtoupper($order->payment_type) }}</p></div>@endif</div><table class="min-w-full text-left text-sm"><thead class="text-xs uppercase tracking-wide text-slate-500"><tr><th class="pb-2">Produk</th><th class="pb-2">Qty</th><th class="pb-2">Harga</th><th class="pb-2 text-right">Subtotal</th></tr></thead><tbody class="divide-y divide-slate-200">@foreach ($order->items as $item)<tr><td class="py-2">{{ $item->product_name ?: ($item->product?->name ?? 'Produk') }}</td><td class="py-2">{{ $item->quantity }}</td><td class="py-2">Rp {{ number_format($item->price, 0, ',', '.') }}</td><td class="py-2 text-right font-medium">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td></tr>@endforeach</tbody></table><div class="mt-3 space-y-2 border-t border-slate-200 pt-3 text-sm"><div class="flex justify-between gap-4 text-slate-600"><span>PB1 / Pajak penjualan</span><span>Rp {{ number_format((float) ($order->total_tax ?: ($order->tax_amount ?: $order->items->sum(fn ($item) => $item->taxAmount()))), 0, ',', '.') }}</span></div><div class="flex justify-between gap-4 font-bold text-slate-900"><span>TOTAL</span><span>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span></div></div></div></details>@empty<p class="px-5 py-8 text-center text-sm text-slate-500">Tidak ada transaksi pada periode ini.</p>@endforelse</div></section>
    </main>
</x-admin-layout>
