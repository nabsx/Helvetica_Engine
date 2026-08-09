<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Inventory Produk — Helvetica POS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">
<div class="mx-auto max-w-7xl px-6 py-8">
    <nav class="mb-6 flex flex-wrap gap-2 text-sm"><a href="{{ route('admin.dashboard') }}" class="rounded-lg bg-white px-3 py-2 font-semibold text-slate-600 shadow-sm">Dashboard</a><a href="{{ route('admin.categories.index') }}" class="rounded-lg bg-white px-3 py-2 font-semibold text-slate-600 shadow-sm">Kategori</a><a href="{{ route('pos.index') }}" class="rounded-lg bg-white px-3 py-2 font-semibold text-slate-600 shadow-sm">POS</a></nav>
    <header class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
        <div><p class="text-sm font-semibold uppercase tracking-widest text-emerald-600">Admin panel</p><h1 class="mt-1 text-3xl font-black">Inventory produk</h1><p class="mt-1 text-sm text-slate-500">Kelola harga, stok, gambar, dan status menu.</p></div>
        <div class="flex gap-2"><a href="{{ route('admin.categories.index') }}" class="rounded-xl bg-white px-4 py-3 text-sm font-semibold shadow-sm">Kategori</a><a href="{{ route('admin.products.create') }}" class="rounded-xl bg-slate-900 px-4 py-3 text-sm font-bold text-white">+ Tambah produk</a></div>
    </header>

    @if(session('success'))<div class="mt-6 rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="mt-6 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>@endif

    <form class="mt-8 grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-[1fr_240px_auto]">
        <input name="search" value="{{ request('search') }}" placeholder="Cari nama produk..." class="rounded-xl border-slate-300 px-4 py-3 text-sm">
        <select name="category_id" class="rounded-xl border-slate-300 px-4 py-3 text-sm"><option value="">Semua kategori</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>@endforeach</select>
        <button class="rounded-xl bg-slate-800 px-5 py-3 text-sm font-bold text-white">Filter</button>
    </form>

    <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @forelse($products as $product)
            <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                @if($product->image)<img src="{{ $product->image }}" alt="{{ $product->name }}" class="h-44 w-full object-cover">@else<div class="flex h-44 items-center justify-center bg-slate-100 text-sm text-slate-400">Belum ada gambar</div>@endif
                <div class="p-4"><div class="flex items-start justify-between gap-3"><div><p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ $product->category?->name }}</p><h2 class="mt-1 font-bold">{{ $product->name }}</h2></div><span class="rounded-full px-2 py-1 text-xs font-bold {{ $product->is_available && $product->stock > 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }}">{{ $product->is_available && $product->stock > 0 ? 'Aktif' : 'Habis' }}</span></div><p class="mt-4 text-lg font-black text-emerald-700">Rp{{ number_format($product->price, 0, ',', '.') }}</p><div class="mt-2 flex justify-between text-sm {{ $product->stock <= $product->low_stock_threshold ? 'font-bold text-amber-600' : 'text-slate-500' }}"><span>Stok</span><span>{{ $product->stock }} unit</span></div><div class="mt-4 flex gap-2"><a href="{{ route('admin.products.edit', $product) }}" class="flex-1 rounded-lg bg-slate-100 px-3 py-2 text-center text-sm font-semibold">Edit</a><form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Hapus produk ini?')">@csrf @method('DELETE')<button class="rounded-lg bg-red-50 px-3 py-2 text-sm font-semibold text-red-600">Hapus</button></form></div></div>
            </article>
        @empty
            <div class="rounded-2xl bg-white p-8 text-sm text-slate-500 sm:col-span-2 lg:col-span-3 xl:col-span-4">Belum ada produk.</div>
        @endforelse
    </div>
    <div class="mt-6">{{ $products->links() }}</div>
</div>
</body>
</html>
