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
<body class="min-h-screen bg-[#f7f9fb] text-slate-900 antialiased">
    <div class="min-h-screen lg:flex">
        <aside class="w-full border-b border-slate-200 bg-white lg:min-h-screen lg:w-72 lg:border-b-0 lg:border-r">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-6">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 text-lg font-black tracking-tight"><span class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-600 text-sm text-white">H</span><span>Helvetica <span class="text-emerald-600">POS</span></span></a>
            </div>
            @php $pendingCancellations = \App\Models\OrderCancellationRequest::query()->pending()->count(); @endphp
            <div class="px-5 py-6"><p class="px-3 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Workspace</p>
                <nav class="mt-3 space-y-1">
                    @foreach([
                        ['admin.dashboard', 'Dashboard'], ['admin.products.index', 'Produk & harga'], ['admin.categories.index', 'Kategori'], ['admin.sales-report', 'Laporan'], ['admin.cancellations.index', 'Pembatalan'], ['admin.shifts.index', 'Cash monitoring'], ['admin.users.index', 'Staff'], ['pos.index', 'Buka POS'],
                    ] as [$route, $label])
                        <a href="{{ route($route) }}" class="flex items-center justify-between rounded-xl px-3 py-3 text-sm font-semibold transition {{ request()->routeIs($route) ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"><span>{{ $label }}</span>@if($route === 'admin.cancellations.index' && $pendingCancellations > 0)<span class="rounded-full bg-red-500 px-2 py-0.5 text-[11px] font-bold text-white">{{ $pendingCancellations }}</span>@endif</a>
                    @endforeach
                </nav>
            </div>
            <div class="hidden border-t border-slate-100 p-5 lg:block"><p class="px-3 text-xs text-slate-400">Sesi admin aktif</p><form class="mt-3" method="POST" action="{{ route('pos.logout') }}">@csrf<button class="w-full rounded-xl px-3 py-2.5 text-left text-sm font-semibold text-slate-500 hover:bg-red-50 hover:text-red-600">Keluar dari panel</button></form></div>
        </aside>
        <main class="min-w-0 flex-1"><div class="mx-auto max-w-[1440px] p-5 sm:p-8 lg:p-10">{{ $slot }}</div></main>
    </div>
    @if(class_exists(\Livewire\Livewire::class)) @livewireScripts @endif
</body>
</html>
