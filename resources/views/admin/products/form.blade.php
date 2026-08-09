@php($isEdit = $product->exists)

<div class="grid gap-6 lg:grid-cols-[1fr_360px]">
    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="grid gap-5 sm:grid-cols-2">
            <label class="sm:col-span-2">
                <span class="text-sm font-semibold text-slate-700">Nama produk</span>
                <input name="name" value="{{ old('name', $product->name) }}" required class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3">
                @error('name')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
            </label>

            <label>
                <span class="text-sm font-semibold text-slate-700">Kategori</span>
                <select name="category_id" required class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3">
                    <option value="">Pilih kategori</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('category_id')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
            </label>

            <label>
                <span class="text-sm font-semibold text-slate-700">Harga nett</span>
                <input type="number" name="price" min="0" step="0.01" value="{{ old('price', $product->price) }}" required class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3">
                @error('price')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
            </label>

            <label>
                <span class="text-sm font-semibold text-slate-700">Stok</span>
                <input type="number" name="stock" min="0" value="{{ old('stock', $product->stock) }}" required class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3">
                @error('stock')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
            </label>

            <label>
                <span class="text-sm font-semibold text-slate-700">Batas stok rendah</span>
                <input type="number" name="low_stock_threshold" min="0" value="{{ old('low_stock_threshold', $product->low_stock_threshold) }}" required class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3">
                @error('low_stock_threshold')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
            </label>
        </div>

        <label class="mt-5 flex items-center gap-3 rounded-xl bg-slate-50 p-4">
            <input type="checkbox" name="is_available" value="1" @checked(old('is_available', $product->is_available)) class="h-4 w-4 rounded border-slate-300 text-emerald-600">
            <span>
                <span class="block text-sm font-semibold text-slate-800">Tampilkan di POS</span>
                <span class="block text-xs text-slate-500">Produk stok 0 tetap tidak akan tampil di kasir.</span>
            </span>
        </label>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="font-bold text-slate-900">Gambar produk</h2>
        @if($product->image)
            <img src="{{ $product->image }}" alt="{{ $product->name }}" class="mt-4 aspect-square w-full rounded-xl object-cover">
        @else
            <div class="mt-4 flex aspect-square items-center justify-center rounded-xl bg-slate-100 text-sm text-slate-400">Belum ada gambar</div>
        @endif
        <input type="file" name="image" accept="image/jpeg,image/png,image/webp" class="mt-4 block w-full text-sm text-slate-600">
        <p class="mt-2 text-xs text-slate-500">JPG, PNG, atau WEBP. Maksimal 2 MB.</p>
        @error('image')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
    </section>
</div>

<div class="mt-6 flex items-center justify-end gap-3">
    <a href="{{ route('admin.products.index') }}" class="rounded-xl px-4 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-100">Batal</a>
    <button class="rounded-xl bg-emerald-600 px-5 py-3 text-sm font-bold text-white hover:bg-emerald-700">{{ $isEdit ? 'Simpan perubahan' : 'Tambah produk' }}</button>
</div>
