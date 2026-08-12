@if(class_exists(\Livewire\Livewire::class))
    <x-admin-layout title="Dashboard Admin">
        <livewire:admin-dashboard />
    </x-admin-layout>
@else
    <x-admin-layout title="Dashboard Admin">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div><p class="text-sm font-semibold text-emerald-600">Ringkasan operasional</p><h1 class="mt-1 text-3xl font-black tracking-tight">Dashboard admin</h1><p class="mt-2 text-sm text-slate-500">Pantau penjualan dan stok dalam satu layar.</p></div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.shifts.index') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-center text-sm font-bold text-slate-700 shadow-sm hover:border-emerald-300 hover:text-emerald-700">Cash monitoring</a>
                @if(isset($activeShifts) && $activeShifts->isNotEmpty())
                    <a href="{{ route('admin.shifts.show', $activeShifts->first()) }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-center text-sm font-bold text-slate-700 shadow-sm hover:border-emerald-300 hover:text-emerald-700">Detail shift</a>
                @endif
                <a href="{{ route('admin.products.create') }}" class="rounded-xl bg-emerald-600 px-4 py-2 text-center text-sm font-bold text-white hover:bg-emerald-700">Tambah produk</a>
            </div>
        </div>
        <div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach([['Transaksi', $summary->total_transaksi ?? 0], ['Pendapatan kotor', 'Rp'.number_format($summary->gross ?? 0, 0, ',', '.')], ['PB1 terkumpul', 'Rp'.number_format($summary->pajak ?? 0, 0, ',', '.')], ['Pendapatan bersih', 'Rp'.number_format($summary->dpp ?? 0, 0, ',', '.')]] as [$label, $value])
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm text-slate-500">{{ $label }}</p><p class="mt-3 text-2xl font-black tracking-tight">{{ $value }}</p></div>
            @endforeach
        </div>
        <div class="mt-6 grid gap-6 lg:grid-cols-2">
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><h2 class="font-bold">Pembayaran hari ini</h2><div class="mt-5 grid grid-cols-2 gap-3"><div class="rounded-xl bg-slate-50 p-4"><p class="text-xs text-slate-500">CASH</p><p class="mt-2 text-xl font-black">Rp{{ number_format($cash, 0, ',', '.') }}</p></div><div class="rounded-xl bg-emerald-50 p-4"><p class="text-xs text-emerald-700">QRIS</p><p class="mt-2 text-xl font-black">Rp{{ number_format($qris, 0, ',', '.') }}</p></div></div></section>
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><h2 class="font-bold">Stok perlu perhatian</h2><div class="mt-4 divide-y divide-slate-100">@forelse($lowStock as $product)<div class="flex items-center justify-between py-3"><div><p class="font-semibold">{{ $product->name }}</p><p class="text-xs text-slate-500">{{ $product->category?->name }}</p></div><span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700">{{ $product->stock }} tersisa</span></div>@empty<p class="py-4 text-sm text-slate-500">Semua stok aman.</p>@endforelse</div></section>
        </div>
    </x-admin-layout>
@endif
