<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Admin Panel' }} — Helvetica POS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @if(class_exists(\Livewire\Livewire::class)) @livewireStyles @endif
</head>
<body class="min-h-screen bg-white text-slate-900 antialiased">
    <div class="min-h-screen lg:flex">
        <aside class="w-full border-b border-slate-200 bg-white lg:min-h-screen lg:w-64 lg:border-b-0 lg:border-r">
            <div class="flex items-center justify-between px-6 py-5">
                <a href="{{ route('admin.dashboard') }}" class="text-lg font-black tracking-tight">Helvetica <span class="text-emerald-600">POS</span></a>
            </div>
            @php
                $pendingCancellations = \App\Models\OrderCancellationRequest::query()->pending()->count();
            @endphp
            <nav class="flex gap-2 overflow-x-auto px-4 pb-4 lg:block lg:space-y-1 lg:px-3">
                @foreach([
                    ['admin.dashboard', 'Dashboard'],
                    ['admin.products.index', 'Produk'],
                    ['admin.categories.index', 'Kategori'],
                    ['admin.sales-report', 'Laporan'],
                    ['admin.cancellations.index', 'Pembatalan'],
                    ['pos.index', 'Buka POS'],
                ] as [$route, $label])
                    <a href="{{ route($route) }}" class="flex items-center justify-between whitespace-nowrap rounded-xl px-3 py-2.5 text-sm font-semibold {{ request()->routeIs($route) ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-50' }}">
                        <span>{{ $label }}</span>
                        @if($route === 'admin.cancellations.index' && $pendingCancellations > 0)
                            <span class="ml-2 rounded-full bg-red-600 px-2 py-0.5 text-xs font-bold text-white">{{ $pendingCancellations }}</span>
                        @endif
                    </a>
                @endforeach
            </nav>
            <div class="hidden border-t border-slate-100 p-4 lg:block">
                <form method="POST" action="{{ route('pos.logout') }}">@csrf<button class="w-full rounded-xl px-3 py-2.5 text-left text-sm font-semibold text-slate-500 hover:bg-red-50 hover:text-red-600">Keluar</button></form>
            </div>
        </aside>
        <main class="min-w-0 flex-1 bg-slate-50/70">
            <div class="mx-auto max-w-7xl p-5 sm:p-8">{{ $slot }}</div>
        </main>
    </div>
    @if(class_exists(\Livewire\Livewire::class)) @livewireScripts @endif
</body>
</html>
