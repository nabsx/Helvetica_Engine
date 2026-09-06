<x-admin-layout title="Laporan Penjualan">
    {{-- Alpine.js isn't loaded by the shared admin layout yet, so this page includes it
         directly (collapse plugin must load before Alpine core, both deferred). --}}
    <script defer src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @php
        $periodLabels = ['harian' => 'Harian', 'bulanan' => 'Bulanan', 'tahunan' => 'Tahunan'];
        $periodText = match ($periode) {
            'bulanan' => \Carbon\CarbonImmutable::parse($bulan)->translatedFormat('F Y'),
            'tahunan' => $tahun,
            default => \Carbon\CarbonImmutable::parse($tanggal)->translatedFormat('d F Y'),
        };
        $todayStr = now('Asia/Jakarta')->toDateString();
        $yesterdayStr = now('Asia/Jakarta')->subDay()->toDateString();
        $isToday = $periode === 'harian' && $tanggal === $todayStr;
        $prevDate = \Carbon\CarbonImmutable::parse($tanggal)->subDay()->toDateString();
        $nextDate = \Carbon\CarbonImmutable::parse($tanggal)->addDay()->toDateString();
        $nextDisabled = $nextDate > $todayStr;

        $grossTotal = max(0.01, $laporan['total_pendapatan_kotor']);
        $netMargin = $grossTotal > 0 ? ($laporan['net_profit'] / $grossTotal) * 100 : 0;
        $activeChannels = collect($laporan['breakdown_pembayaran'])->filter(fn ($p) => $p['jumlah_transaksi'] > 0)->count();

        $cards = [
            ['label' => 'Total Transaksi', 'value' => number_format($laporan['total_transaksi'], 0, ',', '.'), 'hint' => $laporan['total_transaksi'].' pesanan selesai'],
            ['label' => 'Pendapatan Kotor', 'value' => 'Rp '.number_format($laporan['total_pendapatan_kotor'], 0, ',', '.'), 'hint' => 'Gross Sales', 'accent' => true],
            ['label' => 'PB1 Terkumpul', 'value' => 'Rp '.number_format($laporan['total_pajak'], 0, ',', '.'), 'hint' => 'Pajak restoran', 'badge' => number_format($laporan['tarif_pajak_efektif'], 1).'%'],
            ['label' => 'Pendapatan Bersih / DPP', 'value' => 'Rp '.number_format($laporan['total_pendapatan_bersih'], 0, ',', '.'), 'hint' => 'Dasar Pengenaan Pajak'],
            ['label' => 'Pembulatan CASH', 'value' => 'Rp '.number_format($laporan['total_uang_pembulatan'], 0, ',', '.'), 'hint' => 'Selisih pembulatan'],
            ['label' => 'Total Expense', 'value' => 'Rp '.number_format($laporan['total_expense'], 0, ',', '.'), 'hint' => 'Pengeluaran kas kecil'],
        ];
    @endphp

    <div class="mb-6 flex flex-col justify-between gap-4 md:flex-row md:items-end">
        <div>
            <div class="mb-1.5 flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200/70 bg-emerald-50 px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-widest text-emerald-700">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span>
                    Analitik Penjualan
                </span>
                <span class="text-xs text-slate-400">•</span>
                <span class="text-xs font-medium text-slate-500">Buku Kas &amp; Transaksi Kasir</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 lg:text-3xl">Laporan Penjualan</h1>
            <p class="mt-1 text-sm text-slate-500">Pantau performa transaksi kasir, pendapatan bersih (DPP), dan rekonsiliasi metode pembayaran.</p>
        </div>
        <div class="flex items-center gap-3 self-start rounded-xl border border-slate-200 bg-white px-4 py-2.5 shadow-sm md:self-auto">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round"></path></svg>
            </div>
            <div>
                <div class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Periode Aktif</div>
                <div class="text-sm font-bold text-slate-900 num-tabular">{{ $periodText }}</div>
            </div>
        </div>
    </div>

    <main class="space-y-6" x-data="{ periode: @js($periode) }">
        <form method="GET" action="{{ route('admin.sales-report') }}" class="space-y-4 rounded-2xl border border-slate-200/90 bg-white p-5 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-4">
                <nav aria-label="Tabs Periode" class="flex items-center space-x-1 rounded-xl bg-slate-100/90 p-1">
                    @foreach ($periodLabels as $key => $label)
                        <button type="button" @click="periode = '{{ $key }}'"
                                :class="periode === '{{ $key }}' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-white/60'"
                                class="rounded-lg px-4 py-1.5 text-xs font-semibold transition-all">{{ $label }}</button>
                    @endforeach
                    <span class="cursor-not-allowed rounded-lg px-4 py-1.5 text-xs font-medium text-slate-300" title="Segera hadir">Mingguan</span>
                    <span class="cursor-not-allowed rounded-lg px-4 py-1.5 text-xs font-medium text-slate-300" title="Segera hadir">Kustom</span>
                </nav>

                @if ($periode === 'harian')
                    <div class="flex items-center gap-1.5 text-xs">
                        <a href="{{ route('admin.sales-report', array_filter(['periode' => 'harian', 'tanggal' => $prevDate, 'kasir' => $kasirId])) }}"
                           class="rounded-lg border border-slate-200 p-1.5 text-slate-500 transition-all hover:bg-slate-100 hover:text-slate-800" title="Hari Sebelumnya">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                        </a>
                        <a href="{{ route('admin.sales-report', array_filter(['periode' => 'harian', 'tanggal' => $yesterdayStr, 'kasir' => $kasirId])) }}"
                           class="rounded-lg px-2.5 py-1 font-medium transition-all {{ $tanggal === $yesterdayStr ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/80 font-semibold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">Kemarin</a>
                        <a href="{{ route('admin.sales-report', array_filter(['periode' => 'harian', 'tanggal' => $todayStr, 'kasir' => $kasirId])) }}"
                           class="rounded-lg px-2.5 py-1 font-medium transition-all {{ $isToday ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/80 font-semibold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">Hari Ini</a>
                        @if ($nextDisabled)
                            <span class="cursor-not-allowed rounded-lg border border-slate-200 p-1.5 text-slate-300" title="Hari Berikutnya">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                            </span>
                        @else
                            <a href="{{ route('admin.sales-report', array_filter(['periode' => 'harian', 'tanggal' => $nextDate, 'kasir' => $kasirId])) }}"
                               class="rounded-lg border border-slate-200 p-1.5 text-slate-500 transition-all hover:bg-slate-100 hover:text-slate-800" title="Hari Berikutnya">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                            </a>
                        @endif
                    </div>
                @endif
            </div>

            <input type="hidden" name="periode" :value="periode">
            <div class="grid grid-cols-1 items-end gap-4 pt-1 sm:grid-cols-2 lg:grid-cols-12">
                <div x-show="periode === 'harian'" class="lg:col-span-3">
                    <label for="tanggal" class="mb-1.5 block text-xs font-semibold text-slate-700">Tanggal Laporan</label>
                    <input id="tanggal" name="tanggal" type="date" value="{{ $tanggal }}"
                           class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-3.5 py-2.5 text-sm font-medium text-slate-900 shadow-sm transition-all focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500">
                </div>
                <div x-show="periode === 'bulanan'" class="lg:col-span-3">
                    <label for="bulan" class="mb-1.5 block text-xs font-semibold text-slate-700">Bulan Laporan</label>
                    <input id="bulan" name="bulan" type="month" value="{{ $bulan }}"
                           class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-3.5 py-2.5 text-sm font-medium text-slate-900 shadow-sm transition-all focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500">
                </div>
                <div x-show="periode === 'tahunan'" class="lg:col-span-3">
                    <label for="tahun" class="mb-1.5 block text-xs font-semibold text-slate-700">Tahun Laporan</label>
                    <input id="tahun" name="tahun" type="number" min="2000" max="2100" value="{{ substr($tahun, 0, 4) }}"
                           class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-3.5 py-2.5 text-sm font-medium text-slate-900 shadow-sm transition-all focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500">
                </div>

                <div x-show="periode === 'harian'" class="lg:col-span-3">
                    <label for="shift-selector" class="mb-1.5 block text-xs font-semibold text-slate-700">Pilihan Shift Kasir</label>
                    <select name="shift" id="shift-selector"
                            class="w-full cursor-pointer rounded-xl border-slate-200 bg-slate-50/50 px-3.5 py-2.5 text-sm font-medium text-slate-800 shadow-sm transition-all focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500">
                        <option value="" {{ ! $shiftId ? 'selected' : '' }}>Semua Shift (Full Day)</option>
                        @foreach ($shiftOptions as $shift)
                            <option value="{{ $shift->id }}" {{ (int) $shiftId === $shift->id ? 'selected' : '' }}>
                                {{ $shift->user->name ?? 'Kasir' }} • {{ $shift->start_time?->timezone('Asia/Jakarta')->format('H:i') }}–{{ $shift->end_time?->timezone('Asia/Jakarta')->format('H:i') ?? 'Berjalan' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="lg:col-span-4">
                    <label for="cashier-selector" class="mb-1.5 block text-xs font-semibold text-slate-700">Filter Kasir</label>
                    <select name="kasir" id="cashier-selector"
                            class="w-full cursor-pointer rounded-xl border-slate-200 bg-slate-50/50 px-3.5 py-2.5 text-sm font-medium text-slate-800 shadow-sm transition-all focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500">
                        <option value="" {{ ! $kasirId ? 'selected' : '' }}>Semua Kasir</option>
                        @foreach ($kasirOptions as $member)
                            <option value="{{ $member->id }}" {{ (int) $kasirId === $member->id ? 'selected' : '' }}>{{ $member->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <button class="flex h-[42px] w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 text-sm font-semibold text-white shadow-sm shadow-emerald-600/30 transition-all hover:bg-emerald-500 active:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                        Tampilkan
                    </button>
                </div>
            </div>
        </form>

        <section class="grid grid-cols-1 gap-3.5 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7">
            @foreach ($cards as $card)
                <article class="flex flex-col justify-between rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition-all hover:border-slate-300">
                    <div class="mb-2 flex items-center justify-between">
                        <span class="text-xs font-medium text-slate-500">{{ $card['label'] }}</span>
                        @isset($card['badge'])
                            <span class="rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-bold text-slate-600">{{ $card['badge'] }}</span>
                        @endisset
                    </div>
                    <div>
                        <div class="{{ isset($card['accent']) ? 'text-2xl' : 'text-lg' }} font-bold tracking-tight text-slate-900 num-tabular">{{ $card['value'] }}</div>
                        <p class="mt-0.5 text-[11px] {{ isset($card['accent']) ? 'text-emerald-600 font-medium' : 'text-slate-400' }}">{{ $card['hint'] }}</p>
                    </div>
                </article>
            @endforeach

            <article class="relative flex flex-col justify-between overflow-hidden rounded-2xl border-2 border-emerald-500/30 bg-gradient-to-b from-emerald-50/40 to-white p-4 shadow-sm shadow-emerald-500/5">
                <div class="pointer-events-none absolute right-0 top-0 h-16 w-16 rounded-bl-full bg-emerald-500/5"></div>
                <div class="mb-2 flex items-center justify-between">
                    <span class="text-xs font-bold text-emerald-900">Net Profit</span>
                    <span class="rounded-full border border-emerald-200 bg-emerald-100 px-1.5 py-0.5 text-[10px] font-bold text-emerald-800">{{ number_format($netMargin, 1) }}%</span>
                </div>
                <div>
                    <div class="text-lg font-bold tracking-tight text-emerald-700 num-tabular">Rp {{ number_format($laporan['net_profit'], 0, ',', '.') }}</div>
                    <p class="mt-0.5 text-[11px] font-medium text-emerald-800/70">Setelah HPP &amp; Biaya</p>
                </div>
            </article>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm">
            <div class="flex flex-col gap-3 border-b border-slate-100 bg-slate-50/30 p-5 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Pendapatan Berdasarkan Metode Pembayaran</h2>
                    <p class="mt-0.5 text-xs text-slate-500">Rekapitulasi omzet dan pembagian settlement per kanal pembayaran.</p>
                </div>
                <span class="inline-flex items-center gap-1.5 self-start rounded-lg border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-700 shadow-sm md:self-auto">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    Semua Saluran ({{ $activeChannels }} Aktif)
                </span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr class="border-b border-slate-200/80 bg-slate-50/75 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                            <th class="px-5 py-3">Metode</th>
                            <th class="px-5 py-3">Jumlah Transaksi</th>
                            <th class="px-5 py-3 text-right">Pendapatan Kotor</th>
                            <th class="px-5 py-3 text-right">Fee Transaksi / MDR</th>
                            <th class="px-5 py-3 text-right">Total Dibayar / Bersih</th>
                            <th class="px-5 py-3 text-right">Porsi (%)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs font-medium text-slate-700">
                        @foreach (['CASH' => ['💵', 'Uang Tunai di Laci Kasir'], 'QRIS' => ['📱', 'QRIS Dinamis / Statis Merchant']] as $method => [$icon, $desc])
                            @php
                                $p = $laporan['breakdown_pembayaran'][$method];
                                $porsi = $grossTotal > 0 ? ($p['total_dibayar'] / $grossTotal) * 100 : 0;
                                $feePct = $p['total_dibayar'] > 0 ? ($p['total_fee'] / $p['total_dibayar']) * 100 : 0;
                            @endphp
                            <tr class="transition-colors hover:bg-slate-50/60">
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-xs font-bold">{{ $icon }}</div>
                                        <div>
                                            <div class="text-sm font-bold text-slate-900">{{ $method }}</div>
                                            <div class="text-[11px] text-slate-400">{{ $desc }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 num-tabular">
                                    <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-700">{{ $p['jumlah_transaksi'] }} transaksi</span>
                                </td>
                                <td class="px-5 py-3.5 text-right font-semibold text-slate-900 num-tabular">Rp {{ number_format($p['total_dibayar'], 0, ',', '.') }}</td>
                                <td class="px-5 py-3.5 text-right text-slate-400 num-tabular">Rp {{ number_format($p['total_fee'], 0, ',', '.') }} <span class="text-[10px] text-slate-400">({{ number_format($feePct, 1) }}%)</span></td>
                                <td class="px-5 py-3.5 text-right font-bold text-slate-900 num-tabular">Rp {{ number_format($p['total_bersih'], 0, ',', '.') }}</td>
                                <td class="px-5 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <span class="text-xs font-bold text-slate-600 num-tabular">{{ number_format($porsi, 1) }}%</span>
                                        <div class="h-1.5 w-16 overflow-hidden rounded-full bg-slate-100">
                                            <div class="h-full rounded-full bg-emerald-500" style="width: {{ min(100, $porsi) }}%"></div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="border-t border-slate-200 bg-slate-50/90 text-xs font-bold text-slate-900">
                        <tr>
                            <td class="px-5 py-3.5 text-[11px] uppercase tracking-wider text-slate-500">Total Seluruh Metode</td>
                            <td class="px-5 py-3.5 num-tabular">{{ $laporan['total_transaksi'] }} Transaksi</td>
                            <td class="px-5 py-3.5 text-right text-sm num-tabular">Rp {{ number_format($laporan['total_pendapatan_kotor'], 0, ',', '.') }}</td>
                            <td class="px-5 py-3.5 text-right text-slate-500 num-tabular">Rp {{ number_format(collect($laporan['breakdown_pembayaran'])->sum('total_fee'), 0, ',', '.') }}</td>
                            <td class="px-5 py-3.5 text-right text-sm text-emerald-700 num-tabular">Rp {{ number_format(collect($laporan['breakdown_pembayaran'])->sum('total_bersih'), 0, ',', '.') }}</td>
                            <td class="px-5 py-3.5 text-right num-tabular">100.0%</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm"
                 x-data="{
                    search: '',
                    page: 1,
                    perPage: 10,
                    total: {{ $laporan['transaksi']->count() }},
                    get totalPages() { return Math.max(1, Math.ceil(this.total / this.perPage)); },
                 }">
            <div class="flex flex-col justify-between gap-4 border-b border-slate-100 p-5 lg:flex-row lg:items-center">
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-base font-bold text-slate-900">Detail Transaksi</h2>
                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-700">{{ $laporan['transaksi']->count() }} Struk</span>
                    </div>
                    <p class="mt-0.5 text-xs text-slate-500">Daftar order lunas pada periode {{ $periodText }}.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2.5">
                    <div class="relative min-w-[240px]">
                        <input x-model="search" type="text" placeholder="Cari No. Struk / Order ID..."
                               class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pl-8 pr-3 text-xs transition-all focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5 text-slate-400">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                        </span>
                    </div>
                    <a href="{{ route('admin.cancellations.index') }}" class="rounded-xl bg-slate-100 px-2.5 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-200 hover:text-slate-800">Lihat Pembatalan / Void</a>
                </div>
            </div>

            <div class="space-y-3.5 bg-slate-50/40 p-5">
                @forelse ($laporan['transaksi'] as $order)
                    @php
                        $orderTax = (float) ($order->total_tax ?: ($order->tax_amount ?: $order->items->sum(fn ($item) => $item->taxAmount())));
                    @endphp
                    <div x-data="{ open: {{ $loop->first ? 'true' : 'false' }} }"
                         x-show="(search === '' || {{ Illuminate\Support\Js::from(Illuminate\Support\Str::lower($order->order_number)) }}.includes(search.toLowerCase())) && (search !== '' || (page - 1) * perPage <= {{ $loop->index }} && {{ $loop->index }} < page * perPage)"
                         class="overflow-hidden rounded-xl bg-white shadow-sm transition-all"
                         :class="open ? 'border-2 border-emerald-500/40' : 'border border-slate-200 hover:border-slate-300'">
                        <div class="flex flex-wrap items-center justify-between gap-3 p-4 sm:p-5" :class="open ? 'border-b border-slate-100' : ''">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl text-sm font-bold" :class="open ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-600'">#{{ $loop->iteration }}</div>
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="font-mono text-sm font-bold tracking-tight text-slate-900">{{ $order->order_number }}</span>
                                        <span class="rounded-full border border-emerald-200 bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-800">Lunas</span>
                                        <span x-show="!open" class="rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-semibold text-blue-700">{{ strtoupper($order->payment_type) }}</span>
                                    </div>
                                    <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                                        <span x-show="open" class="font-semibold text-slate-700">{{ strtoupper($order->payment_type) }}</span>
                                        <span x-show="open">•</span>
                                        <span>{{ $order->created_at?->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</span>
                                        <span>•</span>
                                        <span>Kasir: <strong class="text-slate-700">{{ $order->user->name ?? '—' }}</strong></span>
                                        <span x-show="!open">•</span>
                                        <span x-show="!open">{{ $order->items->count() }} Items</span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="text-right">
                                    <div class="text-xs text-slate-400">Nilai Transaksi</div>
                                    <div class="text-base font-bold text-slate-900 num-tabular">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</div>
                                </div>
                                <button type="button" @click="open = !open"
                                        class="inline-flex items-center gap-1.5 rounded-lg border px-3 py-1.5 text-xs font-semibold transition-all"
                                        :class="open ? 'text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border-emerald-200/80' : 'text-slate-700 bg-white hover:bg-slate-50 border-slate-200 shadow-sm'">
                                    <span x-text="open ? 'Tutup' : 'Lihat Detail'"></span>
                                    <svg class="h-3.5 w-3.5 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                                </button>
                            </div>
                        </div>

                        <div x-show="open" x-collapse class="space-y-4 bg-slate-50/30 p-5">
                            <div class="grid grid-cols-1 gap-3 rounded-xl border border-slate-200/80 bg-slate-100/70 p-3.5 text-xs md:grid-cols-3">
                                <div>
                                    <div class="text-[11px] text-slate-500">METODE PEMBAYARAN</div>
                                    <div class="mt-0.5 text-sm font-bold text-slate-900">{{ strtoupper($order->payment_type) }}</div>
                                </div>
                                @if (strtoupper($order->payment_type) === 'CASH')
                                    <div>
                                        <div class="text-[11px] text-slate-500">UANG DITERIMA</div>
                                        <div class="num-tabular mt-0.5 text-sm font-bold text-slate-900">Rp {{ number_format($order->cash_given ?? 0, 0, ',', '.') }}</div>
                                    </div>
                                    <div>
                                        <div class="text-[11px] text-slate-500">KEMBALIAN</div>
                                        <div class="num-tabular mt-0.5 text-sm font-bold text-emerald-600">Rp {{ number_format($order->change_amount ?? 0, 0, ',', '.') }}</div>
                                    </div>
                                @else
                                    <div class="md:col-span-2">
                                        <div class="text-[11px] text-slate-500">STATUS PEMBAYARAN</div>
                                        <div class="mt-0.5 text-sm font-bold text-emerald-700">Pembayaran {{ strtoupper($order->payment_type) }} Diterima</div>
                                    </div>
                                @endif
                            </div>

                            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
                                <table class="w-full text-left text-xs">
                                    <thead>
                                        <tr class="border-b border-slate-200 bg-slate-50 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                                            <th class="px-4 py-2.5">Produk</th>
                                            <th class="px-4 py-2.5 text-center">Qty</th>
                                            <th class="px-4 py-2.5 text-right">Harga</th>
                                            <th class="px-4 py-2.5 text-right">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @foreach ($order->items as $item)
                                            <tr>
                                                <td class="px-4 py-3 font-semibold text-slate-800">{{ $item->product_name ?: ($item->product?->name ?? 'Produk') }}</td>
                                                <td class="num-tabular px-4 py-3 text-center font-semibold text-slate-700">{{ $item->quantity }}</td>
                                                <td class="num-tabular px-4 py-3 text-right text-slate-600">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                                <td class="num-tabular px-4 py-3 text-right font-bold text-slate-900">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="border-t border-slate-200 bg-slate-50/60 text-xs font-semibold">
                                        <tr>
                                            <td class="px-4 py-2 text-right text-slate-500" colspan="3">Subtotal Item:</td>
                                            <td class="num-tabular px-4 py-2 text-right text-slate-900">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-2 text-right text-slate-500" colspan="3">PB1 / Pajak Penjualan:</td>
                                            <td class="num-tabular px-4 py-2 text-right text-slate-500">Rp {{ number_format($orderTax, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr class="border-t border-slate-200/90 bg-emerald-50/40 text-sm font-bold">
                                            <td class="px-4 py-3 text-right text-xs uppercase tracking-wider text-slate-900" colspan="3">Total Transaksi:</td>
                                            <td class="num-tabular px-4 py-3 text-right text-base text-emerald-700">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <div class="flex items-center justify-between pt-1">
                                <span class="text-xs text-slate-400">Order #{{ $order->id }} @if($order->shift_id) • Shift #{{ $order->shift_id }} @endif</span>
                                <a href="{{ route('orders.receipt', $order) }}" target="_blank"
                                   class="shadow-2xs inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 transition-all hover:bg-slate-50">
                                    <svg class="h-3.5 w-3.5 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                                    Cetak Ulang Struk
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="py-8 text-center text-sm text-slate-500">Tidak ada transaksi pada periode ini.</p>
                @endforelse
            </div>

            @if ($laporan['transaksi']->count() > 0)
                <div class="flex flex-col items-center justify-between gap-2 border-t border-slate-200 bg-white px-5 py-3.5 text-xs text-slate-500 sm:flex-row" x-show="search === ''">
                    <div>Menampilkan <strong x-text="Math.min((page - 1) * perPage + 1, total)"></strong> - <strong x-text="Math.min(page * perPage, total)"></strong> dari <strong x-text="total"></strong> transaksi periode ini</div>
                    <div class="flex items-center gap-1 font-medium">
                        <button type="button" @click="page = Math.max(1, page - 1)" :disabled="page === 1"
                                :class="page === 1 ? 'text-slate-300 cursor-not-allowed' : 'text-slate-600 hover:bg-slate-50'"
                                class="rounded border border-slate-200 px-2.5 py-1">Sebelumnya</button>
                        <span class="rounded border border-emerald-200 bg-emerald-50 px-2.5 py-1 font-bold text-emerald-700" x-text="page"></span>
                        <button type="button" @click="page = Math.min(totalPages, page + 1)" :disabled="page >= totalPages"
                                :class="page >= totalPages ? 'text-slate-300 cursor-not-allowed' : 'text-slate-600 hover:bg-slate-50'"
                                class="rounded border border-slate-200 px-2.5 py-1">Selanjutnya</button>
                    </div>
                </div>
            @endif
        </section>
    </main>
</x-admin-layout>