<x-admin-layout title="Kategori">
    <main class="mx-auto max-w-3xl">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div>
                <p class="text-sm font-semibold uppercase tracking-widest text-emerald-600">
                    Admin panel
                </p>
                <h1 class="mt-1 text-3xl font-black text-slate-900">Kategori menu</h1>
                <p class="mt-1 text-sm text-slate-500">
                    {{ $categories->count() }} kategori terdaftar
                </p>
            </div>

            <a href="{{ route('admin.products.index') }}"
               class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-3 text-sm font-bold text-white transition hover:bg-slate-800">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke produk
            </a>
        </div>

        @if (session('success'))
            <div class="mt-6 flex items-center gap-2 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mt-6 flex items-center gap-2 rounded-xl bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
                {{ session('error') }}
            </div>
        @endif

        {{-- Form tambah kategori --}}
        <form method="POST" action="{{ route('admin.categories.store') }}"
              class="mt-8 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            @csrf
            <div class="flex flex-col gap-3 sm:flex-row">
                <div class="flex-1">
                    <label for="name" class="sr-only">Nama kategori</label>
                    <input id="name" name="name" required placeholder="Tambah kategori baru, misalnya: Kopi"
                           class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm transition focus:border-emerald-500 focus:ring-emerald-500">
                    @error('name')
                        <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <button class="shrink-0 rounded-xl bg-emerald-600 px-6 py-3 text-sm font-bold text-white transition hover:bg-emerald-700">
                    Tambah
                </button>
            </div>
        </form>

        {{-- Daftar kategori --}}
        <div class="mt-6 rounded-2xl border border-slate-200 bg-white shadow-sm">
            @if ($categories->isEmpty())
                <div class="p-10 text-center">
                    <p class="text-sm font-semibold text-slate-700">Belum ada kategori</p>
                    <p class="mt-1 text-sm text-slate-500">Tambahkan kategori pertama lewat form di atas.</p>
                </div>
            @else
                <div class="divide-y divide-slate-100">
                    @foreach ($categories as $category)
                        <div class="group flex flex-col gap-3 p-4 transition hover:bg-slate-50 sm:flex-row sm:items-center sm:gap-4">
                            <form method="POST" action="{{ route('admin.categories.update', $category) }}"
                                  class="flex flex-1 items-center gap-2">
                                @csrf
                                @method('PUT')

                                <label for="name-{{ $category->id }}" class="sr-only">Nama kategori</label>
                                <input id="name-{{ $category->id }}" name="name" value="{{ $category->name }}" required
                                       class="min-w-0 flex-1 rounded-xl border-transparent bg-transparent px-3 py-2 text-sm font-medium text-slate-900 transition group-hover:border-slate-200 group-hover:bg-white focus:border-emerald-500 focus:bg-white focus:ring-emerald-500">

                                <button class="shrink-0 rounded-lg bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-600 opacity-0 transition group-hover:opacity-100 hover:bg-slate-200">
                                    Simpan
                                </button>
                            </form>

                            <div class="flex shrink-0 items-center justify-between gap-3 sm:justify-end">
                                <span class="rounded-full bg-slate-50 px-2.5 py-1 text-xs font-medium text-slate-500">
                                    {{ $category->products_count }} produk
                                </span>

                                <form method="POST" action="{{ route('admin.categories.destroy', $category) }}"
                                      onsubmit="return confirm('Hapus kategori ini?')">
                                    @csrf
                                    @method('DELETE')

                                    <button class="rounded-lg p-2 text-slate-400 transition hover:bg-red-50 hover:text-red-600"
                                            aria-label="Hapus kategori">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </main>
</x-admin-layout>