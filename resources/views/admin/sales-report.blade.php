<x-admin-layout title="Laporan Penjualan">
    <div class="mb-6">
        <p class="text-sm font-semibold text-emerald-600">Analitik penjualan</p>
        <h1 class="mt-1 text-3xl font-black tracking-tight">Laporan penjualan</h1>
        <p class="mt-2 text-sm text-slate-500">Pantau performa transaksi berdasarkan tanggal dan metode pembayaran.</p>
    </div>

    <main class="space-y-6">
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
</x-admin-layout>
