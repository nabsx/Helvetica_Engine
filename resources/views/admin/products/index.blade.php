<x-admin-layout title="Produk & Harga">
<div class="max-w-7xl mx-auto space-y-6">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-slate-500">
        <span>Admin</span>
        <span>/</span>
        <span>Katalog &amp; Menu</span>
        <span>/</span>
        <span class="inline-flex items-center gap-1 text-emerald-700 font-bold bg-emerald-50 border border-emerald-200/80 px-2 py-0.5 rounded-full text-[10px]">
            <span class="w-1 h-1 rounded-full bg-emerald-600"></span>
            Produk &amp; Harga
        </span>
    </div>

    {{-- Page header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight flex items-center gap-3">
                Produk &amp; Harga
                <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 border border-slate-200">{{ $products->total() }} Menu Terdaftar</span>
            </h1>
            <p class="text-sm text-slate-500 mt-1 max-w-2xl">Kelola harga jual, HPP, pajak, stok, dan ketersediaan produk dari satu tempat.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.categories.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-700 text-sm font-semibold hover:bg-slate-50 hover:border-slate-400 transition shadow-sm">
                <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/></svg>
                Kelola Kategori
            </a>
            <a href="{{ route('admin.products.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700 transition shadow-sm shadow-emerald-600/30">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Tambah Produk Baru
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 text-sm font-medium px-4 py-3">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="rounded-lg border border-rose-200 bg-rose-50 text-rose-800 text-sm font-medium px-4 py-3">{{ session('error') }}</div>
    @endif

    {{-- Summary metric cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total Produk --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm flex flex-col justify-between hover:border-slate-300 transition-colors">
            <div class="flex items-start justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Produk</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-bold text-slate-900">{{ $products->total() }} Menu</div>
                <p class="text-xs text-emerald-600 font-medium flex items-center gap-1 mt-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    Dalam {{ $activeCategoryCount }} kategori aktif
                </p>
            </div>
        </div>

        {{-- Status Harga --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm flex flex-col justify-between hover:border-slate-300 transition-colors">
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-1.5">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Status Harga</span>
                    <svg class="w-[15px] h-[15px] text-slate-400 cursor-help" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" title="Harga yang tertera merupakan harga nett, pajak dihitung terpisah saat checkout"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/></svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-bold text-slate-900">Harga Nett</div>
                <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-blue-700 bg-blue-50 border border-blue-200/70 px-2 py-0.5 rounded-md mt-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                    Pajak dihitung saat checkout
                </span>
            </div>
        </div>

        {{-- Kontrol Margin & COGS --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm flex flex-col justify-between hover:border-slate-300 transition-colors">
            <div class="flex items-start justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Kontrol Margin &amp; COGS</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl font-bold text-slate-900">HPP Aktif</span>
                    @if(!is_null($avgMarginPercent))
                        <span class="text-xs font-semibold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200">Avg: ~{{ $avgMarginPercent }}%</span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 font-medium mt-1">Snapshot modal tersimpan per transaksi</p>
            </div>
        </div>

        {{-- Peringatan Stok Rendah --}}
        <div class="bg-white rounded-2xl p-5 border {{ $lowStockProducts->isNotEmpty() ? 'border-amber-200 bg-gradient-to-br from-white via-white to-amber-50/40' : 'border-slate-200' }} shadow-sm flex flex-col justify-between transition-colors">
            <div class="flex items-start justify-between">
                <span class="text-xs font-bold {{ $lowStockProducts->isNotEmpty() ? 'text-amber-700' : 'text-slate-500' }} uppercase tracking-wider">Peringatan Stok Rendah</span>
                <div class="w-8 h-8 rounded-lg {{ $lowStockProducts->isNotEmpty() ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-500' }} flex items-center justify-center shrink-0">
                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-bold {{ $lowStockProducts->isNotEmpty() ? 'text-amber-900' : 'text-slate-900' }}">{{ $lowStockProducts->count() }} Produk Rendah</div>
                <p class="text-xs {{ $lowStockProducts->isNotEmpty() ? 'text-amber-700/80' : 'text-slate-400' }} font-medium mt-1">
                    @if($lowStockProducts->isNotEmpty())
                        {{ $lowStockProducts->first()->name }} sisa {{ $lowStockProducts->first()->stock }} unit (batas &lt; {{ $lowStockProducts->first()->low_stock_threshold }})
                    @else
                        Semua produk stoknya aman
                    @endif
                </p>
            </div>
        </div>
    </div>

    {{-- Filter & Search toolbar --}}
    <form method="GET" action="{{ route('admin.products.index') }}" class="bg-white p-3 sm:p-4 rounded-2xl border border-slate-200 shadow-sm space-y-3">
        <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3">
            <div class="relative flex-1 min-w-[260px]">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5A6.5 6.5 0 114 10.5a6.5 6.5 0 0113 0z"/></svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" id="tableSearch" placeholder="Cari nama produk..."
                    class="w-full pl-10 pr-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 transition placeholder:text-slate-400">
            </div>
            <div class="flex flex-wrap items-center gap-2.5">
                <div class="relative">
                    <select name="category_id" onchange="this.form.submit()" class="appearance-none bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold py-2.5 pl-3 pr-8 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 cursor-pointer">
                        <option value="">Semua Kategori ({{ $categories->count() }})</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }} ({{ $category->products_count }})</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-500">
                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
                <div class="relative">
                    <select name="stock_status" onchange="this.form.submit()" class="appearance-none bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold py-2.5 pl-3 pr-8 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 cursor-pointer">
                        <option value="" @selected(request('stock_status') == '')>Semua Status Stok</option>
                        <option value="ok" @selected(request('stock_status') == 'ok')>Stok Aman</option>
                        <option value="low" @selected(request('stock_status') == 'low')>Stok Rendah</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-500">
                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
                <div class="relative">
                    <select name="sort" onchange="this.form.submit()" class="appearance-none bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold py-2.5 pl-3 pr-8 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 cursor-pointer">
                        <option value="" @selected(request('sort') == '')>Urutkan: Nama (A-Z)</option>
                        <option value="price_desc" @selected(request('sort') == 'price_desc')>Harga: Tertinggi ke Terendah</option>
                        <option value="price_asc" @selected(request('sort') == 'price_asc')>Harga: Terendah ke Tertinggi</option>
                        <option value="margin_desc" @selected(request('sort') == 'margin_desc')>Margin: Tertinggi</option>
                        <option value="stock_asc" @selected(request('sort') == 'stock_asc')>Stok: Paling Sedikit</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-500">
                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7h10M3 12h6m-6 5h14M17 4v16m0 0l-4-4m4 4l4-4"/></svg>
                    </div>
                </div>
                <a href="{{ route('admin.products.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 text-xs font-semibold rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Reset Filter
                </a>
            </div>
        </div>

        @if(request('search') || request('category_id') || request('stock_status') || request('sort'))
            <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-slate-100 text-xs text-slate-500">
                <span>Filter Aktif:</span>
                @if(request('search'))
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 font-medium border border-emerald-200">Cari: "{{ request('search') }}"</span>
                @endif
                @if(request('category_id'))
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 font-medium border border-emerald-200">Kategori: {{ $categories->firstWhere('id', request('category_id'))?->name }}</span>
                @endif
                @if(request('stock_status'))
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 font-medium">Stok: {{ request('stock_status') == 'low' ? 'Rendah' : 'Aman' }}</span>
                @endif
            </div>
        @endif
    </form>

    {{-- Product table --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="py-3.5 pl-4 pr-2 w-10 text-center"><input type="checkbox" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer"></th>
                        <th class="py-3.5 px-3 min-w-[220px]">Produk</th>
                        <th class="py-3.5 px-3 text-right">Harga Jual Nett</th>
                        <th class="py-3.5 px-3 text-right">HPP / Modal</th>
                        <th class="py-3.5 px-3 text-center">Estimasi Margin</th>
                        <th class="py-3.5 px-3 text-center">Status Pajak</th>
                        <th class="py-3.5 px-3 text-center">Status Stok</th>
                        <th class="py-3.5 pr-4 pl-3 text-right w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($products as $product)
                        @php
                            $isLow = $product->stock <= $product->low_stock_threshold;
                            $isCritical = $isLow && $product->low_stock_threshold > 0 && $product->stock <= (int) floor($product->low_stock_threshold / 2);
                            $margin = ($product->cost_price !== null && $product->price > 0)
                                ? round((($product->price - $product->cost_price) / $product->price) * 100)
                                : null;
                            $theme = $product->category?->badgeTheme();
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors group {{ $isCritical ? 'bg-rose-50/40' : ($isLow ? 'bg-amber-50/20' : '') }}">
                            <td class="py-3.5 pl-4 pr-2 text-center">
                                <input type="checkbox" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer">
                            </td>
                            <td class="py-3.5 px-3">
                                <div class="flex items-center gap-3">
                                    @if($product->image)
                                        <div class="w-11 h-11 rounded-xl bg-cover bg-center border {{ $isCritical ? 'border-amber-300 ring-2 ring-amber-400/20' : 'border-slate-200' }} shrink-0 shadow-sm" style="background-image:url('{{ $product->image }}')"></div>
                                    @else
                                        <div class="w-11 h-11 rounded-xl bg-slate-100 border border-slate-200 shrink-0 flex items-center justify-center text-[10px] font-bold text-slate-400">IMG</div>
                                    @endif
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-slate-900 group-hover:text-emerald-700 transition-colors">{{ $product->name }}</span>
                                            @if($isCritical)
                                                <span class="inline-flex items-center gap-1 text-[10px] font-bold text-rose-700 bg-rose-100 px-1.5 py-0.5 rounded border border-rose-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Kritis
                                                </span>
                                            @elseif($isLow)
                                                <span class="inline-flex items-center text-[10px] font-bold text-amber-700 bg-amber-100/90 px-1.5 py-0.5 rounded">Menipis</span>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            @if($product->category)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $theme['bg'] }} {{ $theme['text'] }} border {{ $theme['border'] }}">{{ $product->category->name }}</span>
                                            @endif
                                            <span class="text-[11px] text-slate-400 font-mono">#PRD-{{ str_pad($product->id, 3, '0', STR_PAD_LEFT) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-3 text-right font-bold text-slate-900">Rp{{ number_format($product->price, 0, ',', '.') }}</td>
                            <td class="py-3.5 px-3 text-right text-slate-600 font-medium">Rp{{ number_format($product->cost_price ?? 0, 0, ',', '.') }}</td>
                            <td class="py-3.5 px-3 text-center">
                                @if(!is_null($margin))
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <svg class="w-[14px] h-[14px]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                                        {{ $margin }}% <span class="font-normal text-emerald-600">(Rp{{ number_format($product->price - $product->cost_price, 0, ',', '.') }})</span>
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-3 text-center">
                                @if($product->tax_rate > 0)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 text-slate-700">{{ $product->tax_name }} {{ number_format($product->tax_rate, 2) }}%</span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 text-slate-500">Non-PB1 0.00%</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-3 text-center">
                                @if($isCritical)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-300">
                                        <span class="w-2 h-2 rounded-full bg-rose-600 animate-ping"></span> {{ $product->stock }} unit - Kritis
                                    </span>
                                @elseif($isLow)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span> {{ $product->stock }} unit - Rendah
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span> {{ $product->stock }} unit
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 pr-4 pl-3 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.products.edit', $product) }}" class="p-1.5 text-slate-500 hover:text-emerald-700 hover:bg-emerald-50 rounded-lg transition inline-flex" title="Edit Produk">
                                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Hapus produk {{ $product->name }}?')" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Hapus Produk">
                                            <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-10 text-center text-sm text-slate-500">Belum ada produk yang cocok dengan filter ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination footer --}}
        <div class="p-4 border-t border-slate-200 bg-white flex flex-col sm:flex-row items-center justify-between gap-4 text-xs font-medium text-slate-500">
            <span>Menampilkan <strong class="text-slate-800">{{ $products->count() }}</strong> dari <strong class="text-slate-800">{{ $products->total() }}</strong> total produk</span>
            <div>{{ $products->links() }}</div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('tableSearch');
        if (searchInput) {
            let debounce;
            searchInput.addEventListener('input', () => {
                clearTimeout(debounce);
                debounce = setTimeout(() => searchInput.form.submit(), 500);
            });
        }
    });
</script>
</x-admin-layout>