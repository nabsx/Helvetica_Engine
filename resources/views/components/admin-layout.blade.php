<!doctype html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Admin Panel' }} — Helvetica POS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif; }
        .nav-scroll::-webkit-scrollbar { width: 4px; }
        .nav-scroll::-webkit-scrollbar-track { background: transparent; }
        .nav-scroll::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 9999px; }
        .nav-scroll::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
    </style>
    @if(class_exists(\Livewire\Livewire::class)) @livewireStyles @endif
</head>
<body class="h-full min-h-screen bg-slate-100 text-slate-700 antialiased">
    @php
        $pendingCancellations = \App\Models\OrderCancellationRequest::query()->pending()->count();
        $productCount = \App\Models\Product::query()->count();
        $shiftOpen = \App\Models\Shift::query()->where('status', 'open')->exists();
        $navSections = [
            'Main Workspace' => [
                ['route' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => 'home'],
                ['route' => 'admin.products.index', 'label' => 'Produk & Harga', 'icon' => 'tag', 'count' => $productCount],
                ['route' => 'admin.categories.index', 'label' => 'Kategori', 'icon' => 'grid'],
            ],
            'Operasional & Kas' => [
                ['route' => 'admin.expenses.index', 'label' => 'Expenses', 'icon' => 'receipt'],
                ['route' => 'admin.sales-report', 'label' => 'Laporan', 'icon' => 'chart', 'tag' => 'Baru'],
                ['route' => 'admin.cancellations.index', 'label' => 'Pembatalan', 'icon' => 'block', 'count' => $pendingCancellations, 'countColor' => 'rose'],
                ['route' => 'admin.shifts.index', 'label' => 'Cash Monitoring', 'icon' => 'cash', 'dot' => $shiftOpen],
            ],
            'Manajemen' => [
                ['route' => 'admin.users.index', 'label' => 'Staff', 'icon' => 'users'],
            ],
        ];
        $icons = [
            'home' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
            'tag' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z',
            'grid' => 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z',
            'receipt' => 'M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z',
            'chart' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
            'block' => 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636',
            'cash' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z',
            'users' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
        ];
    @endphp
    <div class="h-full flex overflow-hidden">
        <aside class="w-72 lg:w-80 h-full flex-shrink-0 bg-white border-r border-slate-200/80 flex flex-col justify-between shadow-sm z-30">
            <div class="p-5 pb-3 border-b border-slate-100 flex-shrink-0">
                <div class="flex items-center justify-between">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-600 to-teal-800 flex items-center justify-center text-white font-extrabold text-xl shadow-md shadow-emerald-700/20 ring-2 ring-emerald-500/20">H</div>
                        <div>
                            <div class="flex items-center gap-1.5 leading-none">
                                <span class="text-lg font-extrabold tracking-tight text-slate-900">Helvetica</span>
                                <span class="text-lg font-extrabold tracking-tight text-emerald-600">POS</span>
                            </div>
                            <p class="text-[11px] font-medium text-slate-400 mt-1">{{ config('app.outlet_name', 'Outlet Semarang') }} • Admin</p>
                        </div>
                    </a>
                    <div class="flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-emerald-50 border border-emerald-200/60 text-[10px] font-semibold text-emerald-700">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        <span>Online</span>
                    </div>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto nav-scroll px-3 py-4 space-y-6">
                @foreach($navSections as $sectionLabel => $items)
                    <div>
                        <div class="px-3 pb-2"><span class="text-[11px] font-bold tracking-wider text-slate-400 uppercase">{{ $sectionLabel }}</span></div>
                        <nav class="space-y-1">
                            @foreach($items as $item)
                                @php $active = request()->routeIs($item['route']); @endphp
                                <a href="{{ route($item['route']) }}"
                                   class="relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition-all group {{ $active ? 'font-semibold text-emerald-800 bg-emerald-50/85 border border-emerald-200/50 shadow-sm' : 'font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-50 border border-transparent' }}">
                                    @if($active)<span class="absolute left-0 top-2 bottom-2 w-1 bg-emerald-600 rounded-r-full"></span>@endif
                                    <svg class="w-5 h-5 flex-shrink-0 transition-colors {{ $active ? 'text-emerald-600' : 'text-slate-400 group-hover:text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path d="{{ $icons[$item['icon']] }}" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
                                    </svg>
                                    <span class="{{ $active ? 'text-emerald-900' : '' }}">{{ $item['label'] }}</span>
                                    @if(!empty($item['tag']))
                                        <span class="ml-auto text-[10px] uppercase font-bold tracking-wider px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800">{{ $item['tag'] }}</span>
                                    @elseif(isset($item['count']) && $item['count'] > 0)
                                        <span class="ml-auto text-[10px] font-bold px-2 py-0.5 rounded-full {{ ($item['countColor'] ?? '') === 'rose' ? 'bg-rose-100 text-rose-700' : 'bg-emerald-200/70 text-emerald-800' }}">{{ $item['count'] }}</span>
                                    @elseif(!empty($item['dot']))
                                        <span class="ml-auto w-2 h-2 rounded-full bg-emerald-500"></span>
                                    @endif
                                </a>
                            @endforeach
                        </nav>
                    </div>
                @endforeach

                <div class="pt-2">
                    <a href="{{ route('pos.index') }}" class="flex items-center justify-between p-3 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-700 hover:to-teal-800 text-white shadow-md shadow-emerald-700/15 transition-all group">
                        <div class="flex items-center gap-3">
                            <div class="p-2 rounded-lg bg-white/10 group-hover:bg-white/20 transition-colors">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
                                </svg>
                            </div>
                            <div>
                                <span class="text-sm font-bold leading-tight block">Buka POS</span>
                                <span class="text-[11px] text-emerald-100 font-normal">Terminal Kasir Siap</span>
                            </div>
                        </div>
                        <svg class="w-4 h-4 text-emerald-200 group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
                        </svg>
                    </a>
                </div>
            </div>

            <div class="p-4 border-t border-slate-200/80 bg-slate-50/70">
                <div class="flex items-center justify-between mb-3 px-1">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span class="text-[11px] font-medium text-slate-500">Sesi admin aktif</span>
                    </div>
                </div>
                <div class="flex items-center justify-between p-2 rounded-xl bg-white border border-slate-200/70 shadow-xs mb-2.5">
                    <div class="flex items-center gap-2.5 overflow-hidden">
                        <div class="w-8 h-8 rounded-lg bg-slate-800 text-slate-100 flex items-center justify-center font-bold text-xs flex-shrink-0">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}</div>
                        <div class="truncate">
                            <div class="text-xs font-bold text-slate-800 truncate">{{ auth()->user()->name ?? 'Admin' }}</div>
                            <div class="text-[10px] text-slate-400 truncate">{{ auth()->user() && auth()->user()->role === 'admin' ? 'Store Manager' : ucfirst(auth()->user()->role ?? '') }}</div>
                        </div>
                    </div>
                    <div class="flex-shrink-0 ml-1"><span class="inline-flex px-1.5 py-0.5 text-[9px] font-medium rounded bg-slate-100 text-slate-600">{{ ucfirst(auth()->user()->role ?? 'Admin') }}</span></div>
                </div>
                <form method="POST" action="{{ route('pos.logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center gap-2 px-3 py-2 text-xs font-semibold text-rose-600 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition-colors group">
                        <svg class="w-4 h-4 text-rose-400 group-hover:text-rose-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
                        </svg>
                        <span>Keluar dari panel</span>
                    </button>
                </form>
            </div>
        </aside>
        <main class="min-w-0 flex-1 h-full overflow-y-auto bg-slate-50/50"><div class="mx-auto max-w-[1440px] p-5 sm:p-8 lg:p-10">{{ $slot }}</div></main>
    </div>
    @if(class_exists(\Livewire\Livewire::class)) @livewireScripts @endif
</body>
</html>