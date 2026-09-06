<div
    wire:poll.15s="refreshDashboard"
    x-data="{ lowStockNotice: null }"
    x-init="$nextTick(() => $wire.refreshDashboard())"
    x-on:low-stock-detected.window="lowStockNotice = $event.detail.products"
    class="relative space-y-6"
>
    {{-- =========================================================
        DASHBOARD PREPARATION
    ========================================================== --}}
    @php
        $isToday = $localDate->toDateString() === now(
            \App\Services\DashboardService::OPERATIONAL_TIMEZONE
        )->toDateString();

        $formulaChip = 'Net = DPP - HPP - expense Jakarta';

        $noteMain = trim(
            str_ireplace(
                $formulaChip . '.',
                '',
                $accountingNote
            )
        );
    @endphp

    {{-- =========================================================
        LOW STOCK NOTIFICATION
    ========================================================== --}}
    <div
        x-cloak
        x-show="lowStockNotice"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="translate-y-2 opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="translate-y-0 opacity-100"
        x-transition:leave-end="translate-y-2 opacity-0"
        x-on:click.outside="lowStockNotice = null"
        class="fixed right-4 top-4 z-40 w-[min(24rem,calc(100vw-2rem))] rounded-2xl border border-rose-200 bg-white p-5 shadow-lg"
        role="status"
        aria-live="polite"
    >
        <div class="flex items-start justify-between gap-4">
            <div>
                <p
                    class="text-base font-bold text-slate-900"
                    x-text="`${lowStockNotice?.length ?? 0} produk stoknya mau habis`"
                ></p>

                <p class="mt-1 text-sm text-slate-500">
                    Perlu diperiksa sebelum transaksi berikutnya.
                </p>
            </div>

            <button
                type="button"
                x-on:click="lowStockNotice = null"
                class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                aria-label="Tutup notifikasi stok menipis"
            >
                <span aria-hidden="true" class="text-xl leading-none">
                    &times;
                </span>
            </button>
        </div>

        <div class="mt-4 divide-y divide-slate-100 rounded-xl border border-rose-100 bg-rose-50/60">
            <template
                x-for="product in (lowStockNotice ?? [])"
                :key="product.id"
            >
                <div class="flex items-center justify-between gap-3 px-3 py-2.5 text-sm">
                    <span
                        class="min-w-0 truncate font-semibold text-slate-700"
                        x-text="product.name"
                    ></span>

                    <span
                        class="shrink-0 font-mono tabular-nums font-bold text-rose-600"
                        x-text="`${product.stock} tersisa`"
                    ></span>
                </div>
            </template>
        </div>

        <a
            href="{{ route('admin.products.index') }}"
            x-on:click="lowStockNotice = null"
            class="mt-4 inline-flex text-sm font-bold text-emerald-600 hover:text-emerald-700"
        >
            Lihat semua
        </a>
    </div>

    {{-- =========================================================
        HEADER
    ========================================================== --}}
    <header class="flex flex-col gap-4 border-b border-slate-200/80 pb-6 md:flex-row md:items-center md:justify-between">
        <div>
            <div class="mb-1 flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-emerald-700">
                <span class="inline-flex h-2 w-2 animate-pulse items-center justify-center rounded-full bg-emerald-500"></span>

                <span>HELVETICA POS</span>

                <span class="text-slate-300">/</span>

                <span class="text-slate-500">OPERATIONS</span>
            </div>

            <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                Dashboard
            </h1>

            <p class="mt-0.5 flex items-center gap-1.5 text-sm text-slate-500">
                <svg
                    class="inline h-4 w-4 text-slate-400"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                    ></path>
                </svg>

                Ringkasan operasional dalam zona waktu
                <span class="font-medium text-slate-700">
                    Asia/Jakarta (WIB)
                </span>
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <div class="relative">
                <label class="sr-only" for="dashboard-period">
                    Periode dashboard
                </label>

                <select
                    id="dashboard-period"
                    wire:model.live="period"
                    class="cursor-pointer appearance-none rounded-xl border border-slate-200 bg-white py-2 pl-3.5 pr-9 text-sm font-medium text-slate-700 shadow-sm transition hover:border-slate-300 focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                >
                    <option value="today">Hari ini</option>
                    <option value="custom">Tanggal dipilih</option>
                </select>

                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-slate-400">
                    <svg
                        class="h-4 w-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            d="M19 9l-7 7-7-7"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                        ></path>
                    </svg>
                </div>
            </div>

            <div class="relative">
                <div class="flex items-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm">
                    <svg
                        class="mr-2 h-4 w-4 text-slate-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                        ></path>
                    </svg>

                    <label class="sr-only" for="dashboard-date">
                        Tanggal dashboard
                    </label>

                    <input
                        id="dashboard-date"
                        wire:model.live="tanggal"
                        type="date"
                        class="w-auto border-none bg-transparent p-0 text-sm font-medium text-slate-700 focus:ring-0"
                    >
                </div>
            </div>

            <button
                type="button"
                wire:click="refreshDashboard"
                wire:loading.attr="disabled"
                class="inline-flex items-center gap-2 rounded-xl bg-emerald-700 px-4 py-2 text-sm font-semibold text-white shadow-sm shadow-emerald-700/20 transition-all duration-150 ease-in-out hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600/30 active:bg-emerald-900 disabled:opacity-60"
            >
                <svg
                    class="h-4 w-4 text-emerald-100"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                    ></path>
                </svg>

                <span wire:loading.remove>Refresh</span>
                <span wire:loading>Memuat...</span>
            </button>
        </div>
    </header>

    {{-- =========================================================
        ACCOUNTING FORMULA ALERT
    ========================================================== --}}
    <div
        x-data="{ dismissed: false }"
        x-show="!dismissed"
        x-cloak
        class="flex items-start justify-between gap-3 rounded-2xl border border-amber-200/90 bg-amber-50/80 p-4 text-sm text-amber-900 shadow-sm sm:items-center"
    >
        <div class="flex items-center gap-3">
            <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-xl bg-amber-100/90 text-amber-700">
                <svg
                    class="h-4 w-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                    ></path>
                </svg>
            </div>

            <p class="leading-relaxed">
                <strong class="font-semibold text-amber-950">
                    Formula Kalkulasi:
                </strong>

                {{ $noteMain }}

                <code class="ml-1 rounded-md bg-amber-100/80 px-2 py-0.5 font-mono text-xs font-semibold text-amber-900">
                    {{ $formulaChip }}
                </code>
            </p>
        </div>

        <button
            type="button"
            x-on:click="dismissed = true"
            class="flex-shrink-0 p-1 text-amber-500 transition hover:text-amber-800"
            title="Tutup"
        >
            <svg
                class="h-4 w-4"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path
                    d="M6 18L18 6M6 6l12 12"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                ></path>
            </svg>
        </button>
    </div>

    {{-- =========================================================
        KPI CARDS
    ========================================================== --}}
    <section
        class="grid grid-cols-2 gap-3.5 sm:gap-4 md:grid-cols-3 lg:grid-cols-6"
        aria-label="Key performance indicators"
    >
        {{-- Sales --}}
        <div class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm transition-shadow hover:shadow-md">
            <div class="pointer-events-none absolute right-0 top-0 -z-0 h-16 w-16 rounded-bl-full bg-emerald-50"></div>

            <div class="relative z-10">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">
                    Sales
                </span>

                <div class="mt-1 font-mono text-xl font-bold tabular-nums text-emerald-700 sm:text-2xl">
                    Rp{{ number_format($revenue, 0, ',', '.') }}
                </div>

                <div class="mt-2 flex items-center gap-1.5">
                    <span class="text-xs text-slate-500">
                        Business revenue
                    </span>
                </div>
            </div>
        </div>

        {{-- Orders --}}
        <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm transition-shadow hover:shadow-md">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">
                Orders
            </span>

            <div class="mt-1 font-mono text-xl font-bold tabular-nums text-slate-900 sm:text-2xl">
                {{ number_format($orderCount, 0, ',', '.') }}
            </div>

            <div class="mt-2 flex items-center gap-1 text-xs text-slate-500">
                <span class="inline-block h-2 w-2 rounded-full bg-emerald-500"></span>
                <span>Paid orders</span>
            </div>
        </div>

        {{-- AOV --}}
        <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm transition-shadow hover:shadow-md">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">
                AOV
            </span>

            <div class="mt-1 font-mono text-xl font-bold tabular-nums text-slate-900 sm:text-2xl">
                {{ $aov === null ? 'N/A' : 'Rp' . number_format($aov, 0, ',', '.') }}
            </div>

            <div class="mt-2 truncate text-xs text-slate-500">
                Average order value
            </div>
        </div>

        {{-- Gross --}}
        <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm transition-shadow hover:shadow-md">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">
                Gross
            </span>

            <div class="mt-1 font-mono text-xl font-bold tabular-nums text-slate-900 sm:text-2xl">
                Rp{{ number_format($gross['value'], 0, ',', '.') }}
            </div>

            <div class="mt-2 truncate text-xs text-slate-500">
                {{ $gross['status'] }}
            </div>
        </div>

        {{-- Expenses --}}
        <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm transition-shadow hover:shadow-md">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">
                Expenses
            </span>

            <div class="mt-1 font-mono text-xl font-bold tabular-nums text-slate-800 sm:text-2xl">
                Rp{{ number_format($expenses['value'], 0, ',', '.') }}
            </div>

            <div class="mt-2 truncate text-xs text-slate-400">
                {{ $expenses['status'] }}
            </div>
        </div>

        {{-- Net Profit --}}
        <div class="rounded-2xl border border-emerald-200/80 bg-gradient-to-b from-white to-emerald-50/20 p-4 shadow-sm transition-shadow hover:shadow-md">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-800">
                    Net Profit
                </span>

                <span class="rounded bg-emerald-100 px-1.5 py-0.5 text-[10px] font-semibold text-emerald-800">
                    Net
                </span>
            </div>

            <div class="mt-1 font-mono text-xl font-bold tabular-nums text-slate-900 sm:text-2xl">
                Rp{{ number_format($net['value'], 0, ',', '.') }}
            </div>

            <div class="mt-2 truncate text-[11px] text-slate-500">
                {{ $net['status'] }}
            </div>
        </div>
    </section>

    {{-- =========================================================
        SALES TREND
    ========================================================== --}}
    <section class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex flex-col gap-2 pb-6 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">
                    Trend 7 hari
                </span>

                <h2 class="mt-0.5 text-lg font-bold text-slate-900">
                    Sales Overview
                </h2>
            </div>

            <div class="flex items-center gap-3">
                <div class="flex items-center gap-1.5 rounded-lg bg-slate-100 px-2.5 py-1 text-xs text-slate-600">
                    <span class="h-2.5 w-2.5 rounded bg-emerald-600"></span>
                    <span class="font-medium">Revenue only</span>
                </div>

                <span class="text-xs font-medium text-slate-400">
                    Minggu ke-{{ $localDate->isoWeek() }}
                    ({{ $localDate->format('M Y') }})
                </span>
            </div>
        </div>

        @php
            $maxChart = max(1, (float) $chart->max('amount'));
            $chartCount = $chart->count();

            $shortRp = function (float $amount): string {
                if ($amount >= 1000) {
                    return 'Rp'
                        . number_format($amount / 1000, 0, ',', '.')
                        . 'k';
                }

                return 'Rp'
                    . number_format($amount, 0, ',', '.');
            };
        @endphp

        <div class="flex h-64 w-full flex-col justify-end pt-6 sm:h-72">
            <div class="relative flex h-full w-full items-end justify-between gap-3 border-b border-slate-200 pb-2 sm:gap-6">

                <div class="pointer-events-none absolute inset-0 -z-0 flex flex-col justify-between opacity-40">
                    <div class="w-full border-b border-dashed border-slate-200 pb-0.5 text-[10px] text-slate-400">
                        {{ $shortRp($maxChart) }}
                    </div>

                    <div class="w-full border-b border-dashed border-slate-200 pb-0.5 text-[10px] text-slate-400">
                        {{ $shortRp($maxChart * 2 / 3) }}
                    </div>

                    <div class="w-full border-b border-dashed border-slate-200 pb-0.5 text-[10px] text-slate-400">
                        {{ $shortRp($maxChart / 3) }}
                    </div>

                    <div class="w-full text-[10px] text-slate-400">
                        Rp0
                    </div>
                </div>

                @foreach ($chart as $index => $point)
                    @php
                        $isTodayBar = $index === $chartCount - 1;

                        $heightPct = max(
                            2,
                            ((float) $point['amount'] / $maxChart) * 100
                        );
                    @endphp

                    <div class="group relative z-10 flex h-full flex-1 flex-col items-center justify-end">

                        @if ($isTodayBar)
                            <div class="mb-2 flex items-center gap-1.5 whitespace-nowrap rounded-md bg-slate-900 px-2.5 py-1 text-[11px] font-medium text-white shadow-md">
                                <span>Hari ini:</span>

                                <span class="font-semibold text-emerald-400">
                                    Rp{{ number_format($point['amount'], 0, ',', '.') }}
                                </span>
                            </div>
                        @endif

                        <div
                            class="w-full max-w-[68px] rounded-t-lg transition-all duration-200 {{ $isTodayBar ? 'bg-emerald-600 shadow-sm hover:bg-emerald-500' : 'bg-emerald-600/70 hover:bg-emerald-600' }}"
                            style="height: {{ $heightPct }}%"
                            title="Rp{{ number_format($point['amount'], 0, ',', '.') }}"
                        ></div>
                    </div>
                @endforeach
            </div>

            <div class="flex items-center justify-between pt-3 text-xs font-semibold text-slate-400">
                @foreach ($chart as $index => $point)
                    <div class="flex-1 text-center {{ $index === $chartCount - 1 ? 'font-bold text-emerald-700' : '' }}">
                        {{ $point['label'] }}
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- =========================================================
        CASH MONITORING + PAYMENT BREAKDOWN
    ========================================================== --}}
    <section class="grid grid-cols-1 gap-6 md:grid-cols-2">

        {{-- Cash Monitoring --}}
        <div class="flex flex-col justify-between rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6">
            <div>
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">
                            Cash Monitoring
                        </h3>

                        <p class="mt-0.5 text-xs text-slate-400">
                            Expected drawer, bukan revenue.
                        </p>
                    </div>

                    <a
                        href="{{ route('admin.shifts.index') }}"
                        class="flex items-center gap-1 text-xs font-semibold text-emerald-700 hover:text-emerald-800 hover:underline"
                    >
                        Lihat shift

                        <svg
                            class="h-3.5 w-3.5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                d="M9 5l7 7-7 7"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                            ></path>
                        </svg>
                    </a>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse ($cashMonitoring as $row)
                        <div class="flex items-center justify-between py-3">
                            <div>
                                <p class="text-sm font-semibold text-slate-800">
                                    {{ $row['shift']->user?->name ?? 'Staff' }}
                                </p>

                                <p class="mt-0.5 text-xs text-slate-400">
                                    {{ $row['status'] }}
                                    · Cash sales Rp{{ number_format($row['cash_sales'], 0, ',', '.') }}
                                </p>
                            </div>

                            <p class="text-sm font-bold text-slate-900">
                                Rp{{ number_format($row['expected'], 0, ',', '.') }}
                            </p>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center py-7 text-center">
                            <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                                <svg
                                    class="h-6 w-6"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.8"
                                    ></path>
                                </svg>
                            </div>

                            <p class="text-sm font-medium text-slate-600">
                                Tidak ada shift aktif saat ini.
                            </p>

                            <span class="mt-0.5 text-xs text-slate-400">
                                Semua laci kasir telah ditutup atau belum dibuka.
                            </span>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="flex items-center justify-between border-t border-slate-100 pt-3">
                <span class="text-xs font-medium text-slate-500">
                    Status Laci Kasir:
                </span>

                @if ($cashMonitoring->isNotEmpty())
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                        Aktif ({{ $cashMonitoring->count() }})
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600">
                        <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                        Nonaktif
                    </span>
                @endif
            </div>
        </div>

        {{-- Payment Breakdown --}}
        <div class="flex flex-col justify-between rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6">
            <div>
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="text-base font-bold text-slate-900">
                        Payment Breakdown
                    </h3>

                    <p class="mt-0.5 text-xs text-slate-400">
                        Distribusi metode pembayaran pesanan hari ini
                    </p>
                </div>

                <div class="space-y-4 py-4">
                    @foreach ($paymentBreakdown as $type => $payment)
                        @php
                            $isActive = (float) $payment['percent'] > 0;
                        @endphp

                        <div>
                            <div class="mb-1.5 flex items-center justify-between text-sm">
                                <span class="flex items-center gap-2 font-semibold {{ $isActive ? 'text-slate-800' : 'text-slate-400' }}">
                                    <span class="h-2.5 w-2.5 rounded-full {{ $isActive ? 'bg-emerald-600' : 'bg-slate-300' }}"></span>

                                    {{ $type }}
                                </span>

                                <span class="font-medium {{ $isActive ? 'text-slate-700' : 'text-slate-400' }}">
                                    {{ $payment['percent'] }}%
                                    ·

                                    <strong class="font-bold {{ $isActive ? 'text-slate-900' : 'text-slate-400' }}">
                                        Rp{{ number_format($payment['amount'], 0, ',', '.') }}
                                    </strong>
                                </span>
                            </div>

                            <div class="h-2.5 w-full overflow-hidden rounded-full bg-slate-100">
                                <div
                                    class="h-2.5 rounded-full {{ $isActive ? 'bg-emerald-600' : 'bg-slate-300' }}"
                                    style="width: {{ min(100, (float) $payment['percent']) }}%"
                                ></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center justify-between border-t border-slate-100 pt-3 text-xs text-slate-500">
                <span>Total transaksi valid</span>

                <span class="font-semibold text-slate-800">
                    {{ $orderCount }} Pembayaran
                </span>
            </div>
        </div>
    </section>

    {{-- =========================================================
        TOP PRODUCTS + LOW STOCK
    ========================================================== --}}
    <section class="grid grid-cols-1 gap-6 md:grid-cols-2">

        {{-- Top Products --}}
        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-900">
                        Top Products
                    </h3>

                    <p class="mt-0.5 text-xs text-slate-400">
                        Produk paling banyak terjual hari ini
                    </p>
                </div>

                <span class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">
                    {{ $isToday ? 'Hari ini' : $localDate->format('d M Y') }}
                </span>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse ($topProducts as $index => $product)
                    <div class="flex items-center justify-between py-3">
                        <div class="flex items-center gap-3">
                            <span class="flex h-6 w-6 items-center justify-center rounded-lg text-xs font-bold {{ $index === 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                {{ $index + 1 }}
                            </span>

                            <div>
                                <h4 class="text-sm font-semibold leading-tight text-slate-900">
                                    {{ $product->name }}
                                </h4>

                                <p class="text-[11px] text-slate-400">
                                    {{ $product->category_name ?? '—' }}
                                </p>
                            </div>
                        </div>

                        <span class="inline-flex items-center rounded-full border border-emerald-200/60 bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-800">
                            {{ $product->quantity }} terjual
                        </span>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-slate-400">
                        Belum ada penjualan.
                    </p>
                @endforelse
            </div>
        </div>

        {{-- Low Stock --}}
        <div class="flex flex-col justify-between rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6">
            <div>
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">
                            Low Stock
                        </h3>

                        <p class="mt-0.5 text-xs text-slate-400">
                            Monitoring bahan baku &amp; persediaan kritis
                        </p>
                    </div>

                    <a
                        href="{{ route('admin.products.index') }}"
                        class="text-xs font-semibold text-emerald-700 hover:text-emerald-800 hover:underline"
                    >
                        Kelola
                    </a>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse ($lowStock as $product)
                        <div class="flex items-center justify-between gap-3 py-3">
                            <div class="min-w-0">
                                <h4 class="truncate text-sm font-semibold leading-tight text-slate-900">
                                    {{ $product->name }}
                                </h4>

                                <p class="text-[11px] text-slate-400">
                                    {{ $product->category?->name ?? '—' }}
                                </p>
                            </div>

                            <span class="inline-flex flex-shrink-0 items-center rounded-full border border-rose-200/60 bg-rose-50 px-2.5 py-0.5 text-xs font-semibold text-rose-600">
                                {{ $product->stock }} tersisa
                            </span>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center py-10 text-center">
                            <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
                                <svg
                                    class="h-6 w-6"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                    ></path>
                                </svg>
                            </div>

                            <p class="text-sm font-semibold text-slate-800">
                                Semua stok aman.
                            </p>

                            <p class="mx-auto mt-1 max-w-xs text-xs text-slate-400">
                                Tidak ada produk atau bahan baku yang berada di bawah ambang batas minimum saat ini.
                            </p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="flex items-center justify-between border-t border-slate-100 pt-3 text-xs">
                <span class="font-medium text-slate-500">
                    Batas Minimum Notifikasi
                </span>

                <span class="font-semibold text-slate-700">
                    Sesuai threshold per produk
                </span>
            </div>
        </div>
    </section>

    {{-- =========================================================
        RECENT TRANSACTIONS + ACTIVITY
    ========================================================== --}}
    <section class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        {{-- Recent Transactions --}}
        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6 lg:col-span-2">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-900">
                        Recent Transactions
                    </h3>

                    <p class="mt-0.5 text-xs text-slate-400">
                        Daftar transaksi penjualan terbaru
                    </p>
                </div>

                <span class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500">
                    {{ $recentOrders->count() }} Pesanan
                </span>
            </div>

            <div class="mt-2 overflow-x-auto">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr class="border-b border-slate-100 text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                            <th class="px-2 py-3">Order</th>
                            <th class="px-2 py-3">Staff</th>
                            <th class="px-2 py-3">Payment</th>
                            <th class="px-2 py-3 text-right">Total</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 text-xs sm:text-sm">
                        @php
                            $paymentColors = [
                                'CASH' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                                'QRIS' => 'bg-sky-50 text-sky-800 border-sky-200',
                            ];
                        @endphp

                        @forelse ($recentOrders as $order)
                            <tr class="transition-colors hover:bg-slate-50/70">
                                <td class="px-2 py-3.5">
                                    <div class="font-mono text-xs font-semibold text-slate-900">
                                        #{{ $order->order_number ?? $order->id }}
                                    </div>

                                    <div class="mt-0.5 text-[11px] text-slate-400">
                                        {{ $order->created_at?->timezone('Asia/Jakarta')->format('H:i') }}
                                        WIB
                                        ·
                                        {{ $order->items_count ?? $order->items()->count() }}
                                        Item
                                    </div>
                                </td>

                                <td class="px-2 py-3.5 font-medium text-slate-700">
                                    {{ $order->user?->name ?? '—' }}
                                </td>

                                <td class="px-2 py-3.5">
                                    <span class="inline-flex items-center rounded border px-2 py-0.5 text-[11px] font-semibold {{ $paymentColors[$order->payment_type] ?? 'border-slate-200 bg-slate-50 text-slate-700' }}">
                                        {{ $order->payment_type }}
                                    </span>
                                </td>

                                <td class="px-2 py-3.5 text-right font-bold text-slate-900">
                                    Rp{{ number_format((float) $order->total_amount, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="4"
                                    class="py-8 text-center text-slate-400"
                                >
                                    Belum ada transaksi.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Recent Activity --}}
        <div class="flex flex-col justify-between rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6">
            <div>
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">
                            Recent Activity
                        </h3>

                        <p class="mt-0.5 text-xs text-slate-400">
                            Audit log operasional POS
                        </p>
                    </div>

                    <span class="inline-flex items-center rounded bg-slate-100 px-2 py-0.5 text-[10px] font-semibold uppercase text-slate-600">
                        Live
                    </span>
                </div>

                <div class="mt-4 space-y-4">
                    @forelse ($activities as $activity)
                        @php
                            $dotColor = str_contains($activity->action, 'order')
                                ? 'bg-blue-500 ring-blue-50'
                                : (
                                    str_contains($activity->action, 'shift')
                                        ? 'bg-emerald-500 ring-emerald-50'
                                        : 'bg-slate-400 ring-slate-100'
                                );

                            $detail = $activity->subject instanceof \App\Models\Order
                                ? '#' . ($activity->subject->order_number ?? $activity->subject->id)
                                : \Illuminate\Support\Str::of($activity->action)
                                    ->afterLast('.')
                                    ->replace('_', ' ')
                                    ->headline();
                        @endphp

                        <div class="flex items-start gap-3">
                            <div class="mt-1 h-2 w-2 flex-shrink-0 rounded-full ring-4 {{ $dotColor }}"></div>

                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="truncate font-mono text-xs font-bold text-slate-900">
                                        {{ $activity->action }}
                                    </span>

                                    <span class="flex-shrink-0 text-[11px] text-slate-400">
                                        {{ $activity->created_at?->timezone('Asia/Jakarta')->format('H:i') }}
                                    </span>
                                </div>

                                <p class="mt-0.5 truncate text-xs text-slate-500">
                                    {{ $activity->user?->name ?? 'System' }}
                                    ·
                                    {{ $detail }}
                                </p>
                            </div>
                        </div>
                    @empty
                        <p class="py-6 text-center text-sm text-slate-400">
                            Belum ada aktivitas.
                        </p>
                    @endforelse
                </div>
            </div>
        </div>
    </section>
</div>