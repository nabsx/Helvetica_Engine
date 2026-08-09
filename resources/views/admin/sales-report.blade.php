<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Laporan Penjualan — Helvetica POS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-100 text-slate-800">
    <header class="border-b bg-white">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-600">Helvetica POS</p>
                <h1 class="mt-1 text-2xl font-bold">Laporan Penjualan</h1>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-sm text-slate-500">{{ Auth::user()->name }}</span>
                <a href="{{ route('admin.products.index') }}" class="rounded-lg bg-emerald-100 px-4 py-2 text-sm font-semibold text-emerald-800 hover:bg-emerald-200">Produk</a>
                <a href="{{ route('admin.products.create') }}" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">+ Produk</a>
                <a href="{{ route('admin.categories.index') }}" class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-semibold hover:bg-slate-200">Kategori</a>
                <a href="{{ route('pos.index') }}" class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-semibold hover:bg-slate-200">POS</a>
                <form method="POST" action="{{ route('pos.logout') }}">
                    @csrf
                    <button class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Keluar</button>
                </form>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-7xl space-y-6 px-6 py-8">
        <form method="GET" action="{{ route('admin.sales-report') }}" class="flex flex-wrap items-end gap-3 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div>
                <label for="tanggal" class="mb-1 block text-sm font-medium text-slate-600">Tanggal laporan</label>
                <input id="tanggal" name="tanggal" type="date" value="{{ $tanggal }}" class="rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
            </div>
            <button class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-500">Tampilkan</button>
        </form>

        <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
            @php
                $cards = [
                    ['label' => 'Total Transaksi', 'value' => number_format($laporan['total_transaksi'], 0, ',', '.')],
                    ['label' => 'Pendapatan Kotor', 'value' => 'Rp '.number_format($laporan['total_pendapatan_kotor'], 0, ',', '.')],
                    ['label' => 'PB1 Terkumpul', 'value' => 'Rp '.number_format($laporan['total_pajak'], 0, ',', '.')],
                    ['label' => 'Pendapatan Bersih / DPP', 'value' => 'Rp '.number_format($laporan['total_pendapatan_bersih'], 0, ',', '.')],
                    ['label' => 'Pembulatan CASH', 'value' => 'Rp '.number_format($laporan['total_uang_pembulatan'], 0, ',', '.')],
                ];
            @endphp
            @foreach ($cards as $card)
                <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-slate-500">{{ $card['label'] }}</p>
                    <p class="mt-2 text-xl font-bold text-slate-900">{{ $card['value'] }}</p>
                </article>
            @endforeach
        </section>

        <section class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="font-bold">Pendapatan berdasarkan metode pembayaran</h2>
                <p class="mt-1 text-sm text-slate-500">Periode {{ \Carbon\CarbonImmutable::parse($tanggal)->translatedFormat('d F Y') }}</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-5 py-3">Metode</th>
                            <th class="px-5 py-3">Jumlah Transaksi</th>
                            <th class="px-5 py-3">Pendapatan Kotor</th>
                            <th class="px-5 py-3">Total Dibayar</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach (['CASH', 'QRIS'] as $method)
                            @php $payment = $laporan['breakdown_pembayaran'][$method]; @endphp
                            <tr>
                                <td class="px-5 py-4 font-semibold">{{ $method }}</td>
                                <td class="px-5 py-4">{{ number_format($payment['jumlah_transaksi'], 0, ',', '.') }}</td>
                                <td class="px-5 py-4">Rp {{ number_format($payment['total_pendapatan'], 0, ',', '.') }}</td>
                                <td class="px-5 py-4 font-semibold">Rp {{ number_format($payment['total_dibayar'], 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
