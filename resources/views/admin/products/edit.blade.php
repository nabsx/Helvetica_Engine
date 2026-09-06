<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Edit Produk: {{ $product->name }} | Admin POS</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    body { font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; -webkit-font-smoothing: antialiased; }
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #e2e8f0; border-radius: 9999px; }
</style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen antialiased selection:bg-emerald-100 selection:text-emerald-800 pb-20">

<header class="sticky top-0 z-30 bg-white/95 backdrop-blur-md border-b border-slate-200 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 sm:h-20">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.products.index') }}" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors" title="Kembali ke Katalog">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
                <div>
                    <nav class="flex items-center space-x-2 text-xs font-semibold tracking-wider text-slate-400 uppercase mb-0.5">
                        <span>Admin Panel</span>
                        <span>/</span>
                        <a href="{{ route('admin.products.index') }}" class="hover:text-slate-600">Katalog Produk</a>
                        <span>/</span>
                        <span class="text-emerald-600">Edit</span>
                    </nav>
                    <div class="flex items-center gap-3">
                        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Edit Produk: {{ $product->name }}</h1>
                        @if ($product->is_available && $product->stock > 0)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif di Kasir
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-500 border border-slate-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> {{ $product->stock <= 0 ? 'Stok Habis' : 'Nonaktif' }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.products.index') }}" class="px-4 py-2 text-sm font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg border border-slate-200 transition-colors">
                    Batal
                </a>
                <button type="submit" form="product-form" class="inline-flex items-center px-5 py-2 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 rounded-lg shadow-sm transition-colors gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Simpan Perubahan
                </button>
            </div>
        </div>
    </div>
</header>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8">
    @if (session('success'))
        <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 text-sm font-medium px-4 py-3">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-6 rounded-lg border border-rose-200 bg-rose-50 text-rose-800 text-sm font-medium px-4 py-3">
            {{ session('error') }}
        </div>
    @endif

    <form id="product-form" method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.products.form')
    </form>

    {{-- Rendered OUTSIDE #product-form on purpose — it's a separate form
         with its own action/method. See stock-adjustment.blade.php. --}}
    @include('admin.products.stock-adjustment')
</main>

</body>
</html>