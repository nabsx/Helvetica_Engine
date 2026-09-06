{{--
    Stock Adjustment panel.

    IMPORTANT: this partial contains its own <form> (PATCH to
    admin.products.stock.adjust). It must be @include'd OUTSIDE the main
    <form id="product-form"> in edit.blade.php — never inside it. Nesting a
    <form> inside another <form> is invalid HTML; browsers drop the inner
    <form> tag and its inputs/button end up submitting the outer form
    instead, which silently ignores the stock fields. That was the original
    bug: clicking "Simpan" here actually submitted the main product-update
    form (PUT), so stock was never adjusted.

    Uses the same grid + column span as the main form's left column so it
    lines up visually underneath it.
--}}
<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start mt-8">
    <div class="lg:col-span-8">
        <section class="bg-amber-50/30 rounded-xl border border-amber-200/80 p-6 shadow-sm">
            <div class="flex items-center justify-between pb-4 mb-4 border-b border-amber-100">
                <div>
                    <span class="text-[11px] font-bold tracking-wider text-amber-700 uppercase">Stock Adjustment</span>
                    <h2 class="text-lg font-bold text-slate-900">Sesuaikan Stok</h2>
                </div>
                <span class="text-xs bg-amber-100/70 text-amber-800 px-2 py-0.5 rounded font-medium">Audit Trail</span>
            </div>
            <p class="text-xs text-slate-600 mb-5 leading-relaxed">
                Untuk koreksi setelah stock opname atau perbaikan salah input. Setiap perubahan tercatat secara permanen di riwayat sebelah kanan — bukan sekadar menimpa angka lama.
            </p>
            <form method="POST" action="{{ route('admin.products.stock.adjust', $product) }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                @csrf
                @method('PATCH')
                <div class="sm:col-span-3">
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5" for="new_stock">
                        Stok Baru
                    </label>
                    <input type="number" id="new_stock" name="new_stock" min="0" value="{{ $product->stock }}" required
                        class="w-full rounded-lg border-slate-200 text-sm font-bold text-slate-900 focus:border-amber-500 focus:ring-amber-500 bg-white py-2 px-3 shadow-sm">
                    @error('new_stock')<p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-6">
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5" for="note">
                        Alasan Penyesuaian
                    </label>
                    <input type="text" id="note" name="note" placeholder="mis. Stock opname 05/09, barang rusak, restock" required
                        class="w-full rounded-lg border-slate-200 text-sm text-slate-800 focus:border-amber-500 focus:ring-amber-500 bg-white py-2 px-3 shadow-sm">
                    @error('note')<p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-3">
                    <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 text-sm font-semibold text-white bg-amber-600 hover:bg-amber-700 active:bg-amber-800 rounded-lg shadow-sm transition-colors">
                        Simpan
                    </button>
                </div>
            </form>
        </section>
    </div>
</div>