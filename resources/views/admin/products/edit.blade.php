<!doctype html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Edit Produk — Helvetica POS</title><script src="https://cdn.tailwindcss.com"></script></head><body class="min-h-screen bg-slate-100"><main class="mx-auto max-w-6xl px-6 py-8"><p class="text-sm font-semibold uppercase tracking-widest text-emerald-600">Admin panel</p><h1 class="mt-1 text-3xl font-black text-slate-900">Edit produk</h1><form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data" class="mt-8">@csrf @method('PUT') @include('admin.products.form')</form>

<div class="mt-8 grid gap-6 lg:grid-cols-[1fr_360px]">
<section class="rounded-2xl border border-amber-100 bg-amber-50/50 p-6">
    <p class="text-xs font-bold uppercase tracking-[0.16em] text-amber-700">Stock adjustment</p>
    <h2 class="mt-1 text-lg font-black text-slate-950">Sesuaikan stok</h2>
    <p class="mt-1 text-sm text-slate-500">Untuk koreksi setelah stock opname atau salah input. Setiap perubahan tercatat di riwayat di sebelah kanan — bukan menimpa angka lama.</p>
    <form method="POST" action="{{ route('admin.products.stock.adjust', $product) }}" class="mt-5 grid gap-4 sm:grid-cols-[160px_1fr_auto] sm:items-end">
        @csrf
        @method('PATCH')
        <label><span class="text-sm font-bold text-slate-700">Stok baru</span><input type="number" name="new_stock" min="0" value="{{ $product->stock }}" required class="mt-2 w-full rounded-xl border-amber-200 bg-white px-4 py-3"></label>
        <label><span class="text-sm font-bold text-slate-700">Alasan</span><input type="text" name="note" placeholder="mis. Stock opname 05/09" required class="mt-2 w-full rounded-xl border-amber-200 bg-white px-4 py-3"></label>
        <button class="rounded-xl bg-amber-600 px-5 py-3 text-sm font-bold text-white shadow-sm hover:bg-amber-700">Simpan</button>
    </form>
    @error('new_stock')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
    @error('note')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
</section>
<section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
    <h2 class="font-black">Riwayat stok terbaru</h2>
    <ul class="mt-4 space-y-3 text-sm">
        @forelse($movements as $movement)
        <li class="flex items-center justify-between border-b border-slate-100 pb-2 last:border-0">
            <span>
                <span class="block font-semibold">{{ strtoupper($movement->type) }}</span>
                <span class="block text-xs text-slate-400">{{ $movement->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} · {{ $movement->user->name ?? '—' }}{{ $movement->note ? ' · '.$movement->note : '' }}</span>
            </span>
            <span class="font-mono font-bold {{ $movement->quantity_delta >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                {{ $movement->quantity_delta >= 0 ? '+' : '' }}{{ $movement->quantity_delta }}
            </span>
        </li>
        @empty
        <li class="text-slate-400">Belum ada riwayat pergerakan stok.</li>
        @endforelse
    </ul>
</section>
</div>
</main></body></html>
