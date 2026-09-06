@php
    $isEdit = $product->exists;
    $margin = null;
    if ($isEdit && (float) $product->price > 0) {
        $marginRp = (float) $product->price - (float) $product->cost_price;
        $margin = [
            'rp' => $marginRp,
            'pct' => round(($marginRp / (float) $product->price) * 100),
        ];
    }
@endphp

<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
    {{-- LEFT COLUMN: Primary form fields --}}
    <div class="lg:col-span-8 space-y-6">

        {{-- SECTION 1: Product Identity --}}
        <section class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
            <div class="pb-4 mb-5 border-b border-slate-100">
                <span class="text-[11px] font-bold tracking-wider text-emerald-600 uppercase">Product Identity</span>
                <h2 class="text-lg font-bold text-slate-900">Informasi Produk</h2>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5" for="name">
                        Nama Produk <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="name" name="name" value="{{ old('name', $product->name) }}"
                        placeholder="Contoh: Iced Latte, Croissant Butter" required
                        class="w-full rounded-lg border-slate-200 text-sm font-medium text-slate-800 focus:border-emerald-500 focus:ring-emerald-500 bg-slate-50/50 py-2.5 px-3.5">
                    @error('name')<p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5" for="category_id">
                            Kategori <span class="text-rose-500">*</span>
                        </label>
                        <select id="category_id" name="category_id" required
                            class="w-full rounded-lg border-slate-200 text-sm font-medium text-slate-800 focus:border-emerald-500 focus:ring-emerald-500 bg-slate-50/50 py-2.5 px-3.5 pr-8 appearance-none">
                            <option value="">Pilih kategori</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id')<p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    @if ($isEdit)
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                                Stok Saat Ini
                            </label>
                            <div class="flex items-center justify-between px-3.5 py-2.5 bg-slate-100/80 border border-slate-200 rounded-lg text-slate-700">
                                <span class="font-bold text-slate-900 text-base">{{ $product->stock }} <span class="text-xs font-normal text-slate-500">cup / serving</span></span>
                                <span class="text-[11px] font-medium text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">Dikelola via Log</span>
                            </div>
                            <p class="text-[11px] text-slate-400 mt-1.5 leading-relaxed">
                                Stok tidak diubah dari form ini — gunakan panel <em>"Sesuaikan Stok"</em> di bawah supaya perubahannya tercatat di riwayat audit.
                            </p>
                        </div>
                    @else
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5" for="stock">
                                Stok Awal <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" id="stock" name="stock" min="0" value="{{ old('stock', $product->stock) }}" required
                                class="w-full rounded-lg border-slate-200 text-sm font-semibold text-slate-900 focus:border-emerald-500 focus:ring-emerald-500 bg-slate-50/50 py-2.5 px-3.5">
                            @error('stock')<p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>@enderror
                        </div>
                    @endif
                </div>
            </div>
        </section>

        {{-- SECTION 2: Product Media --}}
        <section class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
            <div class="flex items-center justify-between pb-4 mb-5 border-b border-slate-100">
                <div>
                    <span class="text-[11px] font-bold tracking-wider text-emerald-600 uppercase">Product Media</span>
                    <h2 class="text-lg font-bold text-slate-900">Gambar Produk</h2>
                </div>
                <span class="text-xs text-slate-400">JPG, PNG, atau WebP maks 2 MB</span>
            </div>
            <div class="flex flex-col sm:flex-row items-center sm:items-start gap-5">
                <div class="relative w-28 h-28 rounded-xl overflow-hidden border border-slate-200 shadow-sm bg-slate-100 flex-shrink-0 flex items-center justify-center">
                    @if ($product->image)
                        <img src="{{ $product->image }}" alt="Foto {{ $product->name ?: 'produk' }}" class="w-full h-full object-cover">
                    @else
                        <span class="text-[10px] font-bold uppercase tracking-wide text-slate-400 text-center px-2">Belum ada foto</span>
                    @endif
                </div>
                <div class="flex-1 w-full">
                    <div class="border-2 border-dashed border-slate-200 hover:border-emerald-400 rounded-xl p-4 bg-slate-50/50 transition-colors flex flex-col justify-center">
                        <div class="flex items-center gap-3">
                            <label class="cursor-pointer inline-flex items-center px-4 py-2 bg-white border border-slate-300 rounded-lg text-xs font-semibold text-slate-700 hover:bg-slate-50 shadow-sm transition-colors" for="image">
                                Pilih File Baru
                            </label>
                            <input class="sr-only" id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp">
                            <span class="text-xs text-slate-500 truncate" id="file-chosen-label">No file chosen</span>
                        </div>
                        <p class="text-xs text-slate-400 mt-2">
                            @if ($product->image)
                                Pilih file baru untuk mengganti gambar saat ini. Resolusi rekomendasi 800x800 px dengan rasio 1:1.
                            @else
                                Resolusi rekomendasi 800x800 px dengan rasio 1:1.
                            @endif
                        </p>
                    </div>
                    @error('image')<p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        {{-- SECTION 3: Pricing & Margin --}}
        <section class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-50 rounded-full blur-3xl -z-10"></div>
            <div class="flex items-center justify-between pb-4 mb-5 border-b border-slate-100">
                <div>
                    <span class="text-[11px] font-bold tracking-wider text-emerald-600 uppercase">Margin Control</span>
                    <h2 class="text-lg font-bold text-slate-900">Harga &amp; HPP</h2>
                </div>
                @if ($margin)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        Gross Margin: {{ $margin['pct'] }}% (Rp {{ number_format($margin['rp'], 0, ',', '.') }})
                    </span>
                @endif
            </div>
            <p class="text-xs text-slate-500 mb-4">
                Harga jual disimpan sebagai harga nett. HPP akan disnapshot saat transaksi untuk perhitungan COGS yang akurat.
            </p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5" for="price">
                        Harga Jual Nett <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative rounded-lg shadow-sm">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <span class="text-slate-400 text-sm font-semibold">Rp</span>
                        </div>
                        <input type="number" id="price" name="price" min="0" step="0.01" value="{{ old('price', $product->price) }}" required
                            class="block w-full rounded-lg border-slate-200 pl-11 text-sm font-semibold text-slate-900 focus:border-emerald-500 focus:ring-emerald-500 bg-slate-50/50 py-2.5">
                    </div>
                    @error('price')<p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5" for="cost_price">
                        HPP / Unit (Modal) <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative rounded-lg shadow-sm">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <span class="text-slate-400 text-sm font-semibold">Rp</span>
                        </div>
                        <input type="number" id="cost_price" name="cost_price" min="0" step="0.01" value="{{ old('cost_price', $product->cost_price) }}" required
                            class="block w-full rounded-lg border-slate-200 pl-11 text-sm font-semibold text-slate-900 focus:border-emerald-500 focus:ring-emerald-500 bg-slate-50/50 py-2.5">
                    </div>
                    @error('cost_price')<p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        {{-- SECTION 4: Tax Configuration --}}
        <section class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
            <div class="flex items-center justify-between pb-4 mb-5 border-b border-slate-100">
                <div>
                    <span class="text-[11px] font-bold tracking-wider text-emerald-600 uppercase">Tax Configuration</span>
                    <h2 class="text-lg font-bold text-slate-900">Pajak Produk</h2>
                </div>
                <span class="text-xs text-slate-400">Dihitung otomatis per checkout</span>
            </div>
            <p class="text-xs text-slate-500 mb-4">
                Pajak dipisahkan dari fee gateway dan dihitung ulang oleh server saat checkout pelanggan.
            </p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5" for="tax_name">
                        Nama Pajak
                    </label>
                    <input type="text" id="tax_name" name="tax_name" value="{{ old('tax_name', $product->tax_name ?? 'PB1') }}"
                        class="w-full rounded-lg border-slate-200 text-sm font-medium text-slate-800 focus:border-emerald-500 focus:ring-emerald-500 bg-slate-50/50 py-2.5 px-3.5">
                    @error('tax_name')<p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5" for="tax_code">
                        Kode Pajak
                    </label>
                    <input type="text" id="tax_code" name="tax_code" value="{{ old('tax_code', $product->tax_code ?? 'PB1') }}"
                        class="w-full rounded-lg border-slate-200 text-sm font-medium text-slate-800 focus:border-emerald-500 focus:ring-emerald-500 bg-slate-50/50 py-2.5 px-3.5">
                    @error('tax_code')<p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="mb-5">
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5" for="tax_rate">
                    Tarif Pajak (%)
                </label>
                <div class="relative w-full sm:w-1/2">
                    <input type="number" id="tax_rate" name="tax_rate" min="0" max="100" step="0.01" value="{{ old('tax_rate', $product->tax_rate ?? 10) }}"
                        class="w-full rounded-lg border-slate-200 text-sm font-semibold text-slate-800 focus:border-emerald-500 focus:ring-emerald-500 bg-slate-50/50 py-2.5 px-3.5 pr-8">
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 font-semibold text-xs">%</span>
                </div>
                @error('tax_rate')<p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div class="pt-3 border-t border-slate-100">
                <label class="relative flex items-start gap-3 cursor-pointer select-none">
                    <input type="checkbox" name="tax_included" value="1" @checked(old('tax_included', $product->tax_included ?? false))
                        class="mt-0.5 h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                    <div class="text-xs">
                        <span class="font-semibold text-slate-800">Harga sudah termasuk pajak</span>
                        <p class="text-slate-400 mt-0.5 leading-normal">Aktifkan hanya jika harga jual yang dimasukkan sudah mencakup komponen pajak restoran/PPN.</p>
                    </div>
                </label>
            </div>
        </section>

        {{-- SECTION 5 (Stock Adjustment) intentionally lives OUTSIDE this
             file's markup now — see admin.products.stock-adjustment. It has
             its own <form> with a different action/method (PATCH to
             products.stock.adjust), and nesting a <form> inside the main
             #product-form (as it used to be here) is invalid HTML: browsers
             silently drop the inner <form> tag and submit the OUTER form
             instead, so stock changes were never actually saved. --}}
    </div>

    {{-- RIGHT COLUMN: Preview, Ketersediaan, Riwayat --}}
    <div class="lg:col-span-4 space-y-6">
        {{-- Checkout Preview --}}
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
            <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100">
                <h3 class="font-bold text-slate-900 text-sm">Preview Checkout</h3>
                <span class="text-[10px] font-medium uppercase tracking-wider text-slate-400 bg-slate-100 px-2 py-0.5 rounded">Simulasi Kasir</span>
            </div>
            <p class="text-xs text-slate-500 mb-4 leading-relaxed">
                Contoh struktur harga yang dilihat kasir. Nilai final selalu dihitung oleh server.
            </p>
            <div class="bg-slate-50 rounded-lg p-3.5 border border-slate-200/70 space-y-2.5 text-xs">
                <div class="flex justify-between items-center text-slate-600">
                    <span>Harga nett</span>
                    <span class="font-semibold text-slate-900">Rp{{ number_format((float) ($product->price ?? 0), 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center text-slate-600">
                    <span>Pajak produk ({{ $product->tax_code ?? 'PB1' }})</span>
                    <span class="text-emerald-700 font-medium">Dari konfigurasi</span>
                </div>
                <div class="flex justify-between items-center text-slate-600">
                    <span>Gateway fee</span>
                    <span class="text-slate-500 italic">Terpisah</span>
                </div>
                <div class="pt-2 border-t border-slate-200 flex justify-between items-center font-bold text-slate-900 text-sm">
                    <span>Total di Kasir</span>
                    <span class="text-emerald-700">Rp{{ number_format((float) ($product->price ?? 0), 0, ',', '.') }}</span>
                </div>
            </div>
            <div class="mt-4 p-3 bg-slate-900 text-white rounded-lg text-xs">
                <div class="flex items-center gap-1.5 font-bold text-emerald-400 mb-1">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M10 1.944A11.954 11.954 0 012.166 5C2.056 5.649 2 6.319 2 7c0 5.225 3.34 9.67 8 11.317C14.66 16.67 18 12.225 18 7c0-.682-.057-1.35-.166-2.001A11.954 11.954 0 0110 1.944zM11 14a1 1 0 11-2 0 1 1 0 012 0zm0-7a1 1 0 10-2 0v3a1 1 0 102 0V7z" />
                    </svg>
                    <span>Integrity by default</span>
                </div>
                <p class="text-slate-300 text-[11px] leading-relaxed">
                    Harga, pajak, HPP, dan fee disnapshot agar laporan historis tidak berubah ketika terjadi edit produk di kemudian hari.
                </p>
            </div>
        </div>

        {{-- Ketersediaan --}}
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
            <h3 class="font-bold text-slate-900 text-sm mb-4">Ketersediaan</h3>
            <div class="bg-slate-50 border border-slate-200/80 rounded-lg p-3.5 mb-4">
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="is_available" value="1" @checked(old('is_available', $product->is_available))
                        class="mt-0.5 h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                    <div>
                        <span class="text-xs font-semibold text-slate-800 block">Tampilkan di POS</span>
                        <span class="text-[11px] text-slate-400 block mt-0.5">Produk dengan stok 0 tetap tidak akan tampil di kasir walau opsi ini aktif.</span>
                    </div>
                </label>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5" for="low_stock_threshold">
                    Batas Stok Rendah
                </label>
                <div class="relative">
                    <input type="number" id="low_stock_threshold" name="low_stock_threshold" min="0" value="{{ old('low_stock_threshold', $product->low_stock_threshold) }}" required
                        class="w-full rounded-lg border-slate-200 text-sm font-semibold text-slate-900 focus:border-emerald-500 focus:ring-emerald-500 bg-slate-50/50 py-2.5 px-3.5">
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-xs text-slate-400">cup</span>
                </div>
                @error('low_stock_threshold')<p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>@enderror
                <p class="text-[11px] text-slate-400 mt-1">Notifikasi alert kasir akan muncul bila sisa stok di bawah angka ini.</p>
            </div>
        </div>

        @if ($isEdit)
            {{-- Riwayat Stok Terbaru --}}
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
                <div class="pb-3 mb-4 border-b border-slate-100">
                    <h3 class="font-bold text-slate-900 text-sm">Riwayat Stok Terbaru</h3>
                </div>
                <div class="space-y-4 max-h-96 overflow-y-auto custom-scrollbar pr-1">
                    @forelse ($movements as $movement)
                        <div class="flex items-start justify-between gap-3 text-xs pb-3 border-b border-slate-100 last:border-0 last:pb-0">
                            <div class="space-y-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-slate-900 text-[11px] tracking-wide uppercase">{{ $movement->type }}</span>
                                    <span class="text-[10px] text-slate-400">{{ $movement->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}</span>
                                </div>
                                <p class="text-slate-500 text-[11px] leading-tight">
                                    <span class="font-medium text-slate-700">{{ $movement->user->name ?? '—' }}</span>
                                    @if ($movement->note) — {{ $movement->note }} @endif
                                </p>
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold flex-shrink-0 {{ $movement->quantity_delta >= 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                                {{ $movement->quantity_delta >= 0 ? '+' : '' }}{{ $movement->quantity_delta }}
                            </span>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400">Belum ada riwayat pergerakan stok.</p>
                    @endforelse
                </div>
            </div>
        @endif
    </div>
</div>

<script data-purpose="event-handlers">
    (function () {
        var fileInput = document.getElementById('image');
        var fileLabel = document.getElementById('file-chosen-label');
        if (fileInput && fileLabel) {
            fileInput.addEventListener('change', function () {
                if (this.files && this.files.length > 0) {
                    fileLabel.textContent = this.files[0].name;
                    fileLabel.classList.add('text-emerald-700', 'font-medium');
                } else {
                    fileLabel.textContent = 'No file chosen';
                    fileLabel.classList.remove('text-emerald-700', 'font-medium');
                }
            });
        }
    })();
</script>