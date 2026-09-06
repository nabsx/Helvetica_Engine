<x-admin-layout title="Kategori Menu">
<style>
    [data-cat-row].dragging { opacity: 0.4; }
    [data-cat-row] { cursor: default; }
</style>
<div class="max-w-7xl mx-auto space-y-8">

    @if (session('success'))
        <div class="rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm font-medium text-emerald-700 flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="rounded-xl bg-rose-50 border border-rose-200 px-4 py-3 text-sm font-medium text-rose-700 flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- HEADER --}}
    <header class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold tracking-wider text-emerald-600 uppercase mb-1">
                <span>Admin Panel</span>
                <span class="text-slate-300">/</span>
                <a href="{{ route('admin.products.index') }}" class="hover:text-emerald-700">Katalog Produk</a>
                <span class="text-slate-300">/</span>
                <span class="text-slate-500">Kategori</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight flex items-center gap-3">
                Kategori Menu
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    {{ $stats['active'] }} Kategori Aktif
                </span>
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Kelola pengelompokan produk dan urutan tampilan kategori pada antarmuka kasir (POS).
            </p>
        </div>
        <div class="flex items-center gap-3 self-start sm:self-center">
            <a href="{{ route('admin.products.index') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 hover:text-slate-900 transition shadow-sm">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali ke produk
            </a>
        </div>
    </header>

    {{-- QUICK ADD --}}
    <section class="bg-white rounded-2xl p-5 sm:p-6 shadow-sm border border-slate-200">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="max-w-md">
                <h2 class="text-base font-bold text-slate-900">Tambah Kategori Cepat</h2>
                <p class="text-xs text-slate-500 mt-0.5">Kategori baru akan otomatis ditambahkan ke tab paling ujung di tablet POS kasir.</p>
            </div>
            <form method="POST" action="{{ route('admin.categories.store') }}" class="flex-1 max-w-2xl flex flex-col sm:flex-row items-stretch gap-3">
                @csrf
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                    </div>
                    <input name="name" type="text" placeholder="Tambah kategori baru, misalnya: Kopi, Makanan, Dessert..."
                        class="w-full pl-10 pr-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition placeholder:text-slate-400">
                </div>
                <button type="submit" class="inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-sm font-semibold rounded-xl shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Tambah</span>
                </button>
            </form>
        </div>
        @error('name')<p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
    </section>

    <main class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        {{-- LEFT: category list --}}
        <section class="lg:col-span-8 space-y-4">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-4 sm:p-5 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="relative flex-1 max-w-sm">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </span>
                        <input id="category-search" type="text" placeholder="Cari kategori..."
                            class="w-full text-xs sm:text-sm pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition">
                    </div>
                    <form method="GET" action="{{ route('admin.categories.index') }}" class="flex items-center gap-2">
                        <label for="sort" class="text-xs text-slate-500 whitespace-nowrap font-medium">Urutan:</label>
                        <select id="sort" name="sort" onchange="this.form.submit()"
                            class="text-xs font-medium text-slate-700 bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-2 focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="custom" @selected($sort === 'custom')>Urutan Tampilan POS (Kustom)</option>
                            <option value="name" @selected($sort === 'name')>Nama Kategori (A-Z)</option>
                            <option value="count" @selected($sort === 'count')>Produk Terbanyak</option>
                        </select>
                    </form>
                </div>

                <ul id="category-list" class="divide-y divide-slate-100">
                    @forelse ($categories as $category)
                        @php $theme = $category->badgeTheme(); @endphp
                        <li data-cat-row data-id="{{ $category->id }}" data-name="{{ Str::lower($category->name) }}"
                            draggable="{{ $sort === 'custom' ? 'true' : 'false' }}"
                            class="relative p-4 sm:p-5 hover:bg-slate-50/75 transition-colors flex items-center justify-between gap-4 group">
                            <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                                <button type="button" class="drag-handle {{ $sort === 'custom' ? 'cursor-grab' : 'cursor-not-allowed opacity-30' }} text-slate-300 hover:text-slate-500 p-1 -ml-1 rounded focus:outline-none"
                                    title="{{ $sort === 'custom' ? 'Tahan untuk mengubah urutan POS' : 'Pilih \"Urutan Tampilan POS\" untuk mengubah urutan' }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/></svg>
                                </button>

                                <div class="w-10 h-10 rounded-xl {{ $theme['bg'] }} {{ $theme['text'] }} border {{ $theme['border'] }} flex items-center justify-center shrink-0">
                                    @switch($theme['icon'])
                                        @case('cup')
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                            @break
                                        @case('coffee')
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 8h1a4 4 0 010 8h-1M2 8h16v9a4 4 0 01-4 4H6a4 4 0 01-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><line x1="10" y1="1" x2="10" y2="4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><line x1="14" y1="1" x2="14" y2="4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                            @break
                                        @case('flask')
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                                            @break
                                        @case('pastry')
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            @break
                                        @default
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                    @endswitch
                                </div>

                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="cat-name text-sm font-bold text-slate-900 truncate">{{ $category->name }}</span>
                                        <span class="text-[11px] font-mono text-slate-400">#CAT-{{ str_pad($category->id, 3, '0', STR_PAD_LEFT) }}</span>
                                        @if ($topCategoryId === $category->id)
                                            <span class="text-[10px] uppercase font-bold tracking-wider px-1.5 py-0.5 rounded bg-amber-100 text-amber-800">Populer</span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="text-xs text-slate-500">Urutan ke-{{ $loop->iteration }} di POS</span>
                                        <span class="text-slate-300">•</span>
                                        <a href="{{ route('admin.products.index', ['category_id' => $category->id]) }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 hover:underline">
                                            {{ $category->products_count }} produk terdaftar →
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 sm:gap-6">
                                <form method="POST" action="{{ route('admin.categories.toggle', $category) }}" class="hidden sm:flex items-center gap-2 toggle-form" data-toggle-form>
                                    @csrf
                                    @method('PATCH')
                                    <span class="text-xs font-medium text-slate-500">Tampil di POS</span>
                                    <input type="checkbox" onchange="this.form.requestSubmit()" @checked($category->is_active)
                                        class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300">
                                </form>

                                <div class="flex items-center gap-1.5">
                                    <button type="button" data-edit-toggle title="Edit Kategori" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </button>
                                    <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" onsubmit="return confirm('Hapus kategori {{ $category->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus Kategori" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            {{-- Inline edit form, hidden until the pencil icon is clicked --}}
                            <form method="POST" action="{{ route('admin.categories.update', $category) }}" data-edit-form
                                class="hidden absolute inset-x-0 -mt-2 mx-4 sm:mx-5 p-3 bg-white border border-slate-200 rounded-xl shadow-lg flex items-center gap-2 z-10">
                                @csrf
                                @method('PUT')
                                <input name="name" value="{{ $category->name }}" required
                                    class="flex-1 rounded-lg border-slate-200 text-sm px-3 py-2 focus:border-emerald-500 focus:ring-emerald-500">
                                <button type="submit" class="shrink-0 rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700">Simpan</button>
                                <button type="button" data-edit-toggle class="shrink-0 rounded-lg bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-200">Batal</button>
                            </form>
                        </li>
                    @empty
                        <li class="p-10 text-center">
                            <p class="text-sm font-semibold text-slate-700">Belum ada kategori</p>
                            <p class="mt-1 text-sm text-slate-500">Tambahkan kategori pertama lewat form di atas.</p>
                        </li>
                    @endforelse
                </ul>

                <div class="p-4 bg-slate-50/80 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-2">
                    <span>Menampilkan {{ $categories->count() }} dari {{ $categories->count() }} total kategori</span>
                    <div id="save-indicator" class="flex items-center gap-1 font-medium text-slate-600">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Perubahan tersimpan otomatis</span>
                    </div>
                </div>
            </div>
        </section>

        {{-- RIGHT: stats + tips --}}
        <aside class="lg:col-span-4 space-y-6">
            <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4">Statistik Singkat</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <span class="text-sm text-slate-600">Total Kategori</span>
                        <span class="text-base font-bold text-slate-900">{{ $stats['total'] }} Kategori</span>
                    </div>
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <span class="text-sm text-slate-600">Produk Terkategori</span>
                        <span class="text-base font-bold text-emerald-600">{{ $stats['productsTotal'] }} Menu</span>
                    </div>
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <span class="text-sm text-slate-600">Kategori Terlaris</span>
                        @if ($stats['topCategoryName'])
                            <span class="inline-flex items-center gap-1.5 text-sm font-bold text-slate-900">
                                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                {{ $stats['topCategoryName'] }}
                            </span>
                        @else
                            <span class="text-xs text-slate-400">Belum ada data</span>
                        @endif
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-slate-600">Kategori Non-aktif</span>
                        <span class="text-xs font-semibold px-2 py-0.5 rounded bg-slate-100 text-slate-600">{{ $stats['inactive'] }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-slate-900 text-white rounded-2xl p-6 shadow-sm relative overflow-hidden">
                <div class="absolute -right-8 -bottom-8 w-32 h-32 bg-emerald-500/20 rounded-full blur-2xl pointer-events-none"></div>
                <div class="flex items-center gap-2 text-emerald-400 text-xs font-bold uppercase tracking-wider mb-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Tips Tata Letak POS
                </div>
                <h4 class="text-base font-bold text-white mb-2">Urutan Tampilan Kasir</h4>
                <p class="text-xs leading-relaxed text-slate-300 mb-4">
                    Kategori paling atas akan langsung terlihat pada bilah tab cepat di layar kasir. Pilih "Urutan Tampilan POS (Kustom)" lalu tarik dan geser baris daftar kategori di sebelah kiri untuk mengatur urutan prioritas.
                </p>
                @if ($categories->count() > 0)
                    <div class="p-3 bg-slate-800/80 rounded-xl border border-slate-700/80 text-[11px] text-slate-300 space-y-1.5">
                        @foreach ($categories->take(2) as $topCat)
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400">Tab {{ $loop->iteration }} di Layar POS:</span>
                                <span class="font-semibold text-emerald-400">{{ $topCat->name }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="bg-emerald-50 border border-emerald-200/80 rounded-2xl p-5 text-emerald-950">
                <div class="flex items-start gap-3">
                    <div class="p-1.5 bg-emerald-100 rounded-lg text-emerald-700 mt-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <div class="text-xs leading-relaxed">
                        <p class="font-bold text-emerald-900 mb-0.5">Sinkronisasi Instan</p>
                        <p class="text-emerald-800/90">
                            Setiap pembaruan nama, status tampil, atau susunan kategori langsung disinkronkan ke seluruh terminal mesin POS tanpa perlu restart aplikasi.
                        </p>
                    </div>
                </div>
            </div>
        </aside>
    </main>
</div>

<script>
(function () {
    var csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    // Client-side search filter (no server round trip needed for this).
    var searchInput = document.getElementById('category-search');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            var term = this.value.trim().toLowerCase();
            document.querySelectorAll('[data-cat-row]').forEach(function (row) {
                row.style.display = row.dataset.name.includes(term) ? '' : 'none';
            });
        });
    }

    // Inline edit toggle (pencil icon <-> inline rename form).
    document.querySelectorAll('[data-cat-row]').forEach(function (row) {
        var editForm = row.querySelector('[data-edit-form]');
        row.querySelectorAll('[data-edit-toggle]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                editForm.classList.toggle('hidden');
            });
        });
    });

    // Drag & drop reorder — only active when sort = custom.
    var list = document.getElementById('category-list');
    var dragged = null;
    list.querySelectorAll('[draggable="true"]').forEach(function (row) {
        row.addEventListener('dragstart', function () {
            dragged = row;
            row.classList.add('dragging');
        });
        row.addEventListener('dragend', function () {
            row.classList.remove('dragging');
            dragged = null;
            persistOrder();
        });
        row.addEventListener('dragover', function (e) {
            e.preventDefault();
            var after = getRowAfter(list, e.clientY);
            if (!dragged) return;
            if (after == null) {
                list.appendChild(dragged);
            } else {
                list.insertBefore(dragged, after);
            }
        });
    });

    function getRowAfter(container, y) {
        var rows = [].slice.call(container.querySelectorAll('[data-cat-row]:not(.dragging)'));
        return rows.reduce(function (closest, row) {
            var box = row.getBoundingClientRect();
            var offset = y - box.top - box.height / 2;
            if (offset < 0 && offset > closest.offset) {
                return { offset: offset, element: row };
            }
            return closest;
        }, { offset: Number.NEGATIVE_INFINITY, element: null }).element;
    }

    function persistOrder() {
        var order = [].slice.call(list.querySelectorAll('[data-cat-row]')).map(function (row) {
            return parseInt(row.dataset.id, 10);
        });

        var indicator = document.getElementById('save-indicator');
        indicator.querySelector('span').textContent = 'Menyimpan urutan...';

        fetch('{{ route('admin.categories.reorder') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ order: order }),
        }).then(function (res) {
            indicator.querySelector('span').textContent = res.ok
                ? 'Perubahan susunan tersimpan otomatis'
                : 'Gagal menyimpan urutan, muat ulang halaman';
        }).catch(function () {
            indicator.querySelector('span').textContent = 'Gagal menyimpan urutan, muat ulang halaman';
        });
    }

    // Instant "Tampil di POS" toggle via fetch, so the checkbox doesn't
    // force a full page reload.
    document.querySelectorAll('[data-toggle-form]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: new FormData(form),
            });
        });
    });
})();
</script>
</x-admin-layout>