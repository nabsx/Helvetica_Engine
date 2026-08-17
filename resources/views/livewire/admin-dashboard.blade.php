<div wire:poll.15s="refreshDashboard" class="space-y-8">
    <header class="flex flex-col justify-between gap-6 border-b border-slate-200 pb-6 lg:flex-row lg:items-end">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.22em] text-teal-600">Helvetica POS / Operations</p>
            <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-900">Dashboard</h1>
            <p class="mt-1 text-sm text-slate-500">Ringkasan operasional dalam zona waktu Asia/Jakarta.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <label class="sr-only" for="dashboard-period">Periode dashboard</label>
            <select id="dashboard-period" wire:model.live="period" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                <option value="today">Hari ini</option>
                <option value="custom">Tanggal dipilih</option>
            </select>
            <label class="sr-only" for="dashboard-date">Tanggal dashboard</label>
            <input id="dashboard-date" wire:model.live="tanggal" type="date" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-teal-500 focus:ring-teal-500">
            <button type="button" wire:click="refreshDashboard" wire:loading.attr="disabled" class="rounded-xl bg-teal-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-teal-700 disabled:opacity-50">
                <span wire:loading.remove>Refresh</span>
                <span wire:loading>Memuat...</span>
            </button>
        </div>
    </header>

    <div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm leading-6 text-amber-800">
        {{ $accountingNote }}
    </div>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5" aria-label="Key performance indicators">
        @foreach ([
            ['Sales', 'Rp'.number_format($revenue, 0, ',', '.'), 'Business revenue', 'text-teal-600'],
            ['Orders', number_format($orderCount, 0, ',', '.'), 'Paid orders', 'text-slate-900'],
            ['AOV', $aov === null ? 'N/A' : 'Rp'.number_format($aov, 0, ',', '.'), 'Average order value', 'text-slate-900'],
            ['Gross', 'Rp'.number_format($gross['value'], 0, ',', '.'), $gross['status'], 'text-slate-900'],
            ['Net', 'Rp'.number_format($net['value'], 0, ',', '.'), $net['status'], 'text-slate-900'],
        ] as [$label, $value, $hint, $valueColor])
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ $label }}</p>
                <p class="mt-3 text-2xl font-mono tabular-nums font-semibold {{ $valueColor }}">{{ $value }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ $hint }}</p>
            </div>
        @endforeach
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Trend 7 hari</p>
                <h2 class="mt-1 text-lg font-bold text-slate-900">Sales Overview</h2>
            </div>
            <span class="text-xs font-medium text-slate-400">Revenue only</span>
        </div>
        @php($maxChart = max(1, (float) $chart->max('amount')))
        <div class="mt-8 flex h-44 items-end gap-2 sm:gap-4">
            @foreach ($chart as $point)
                <div class="flex min-w-0 flex-1 flex-col items-center gap-2">
                    <div class="w-full rounded-t-lg bg-teal-500 transition hover:bg-teal-600" style="height: {{ max(8, ((float) $point['amount'] / $maxChart) * 132) }}px" title="Rp{{ number_format($point['amount'], 0, ',', '.') }}"></div>
                    <span class="text-[11px] font-medium text-slate-400">{{ $point['label'] }}</span>
                </div>
            @endforeach
        </div>
    </section>

    <div class="grid gap-5 xl:grid-cols-2">
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-bold text-slate-900">Cash Monitoring</h2>
                    <p class="mt-1 text-xs text-slate-500">Expected drawer, bukan revenue.</p>
                </div>
                <a href="{{ route('admin.shifts.index') }}" class="text-xs font-bold text-teal-600 hover:text-teal-700">Lihat shift</a>
            </div>
            <div class="mt-4 divide-y divide-slate-100">
                @forelse ($cashMonitoring as $row)
                    <div class="flex items-center justify-between py-3">
                        <div>
                            <p class="font-semibold text-slate-700">{{ $row['shift']->user?->name ?? 'Staff' }}</p>
                            <p class="text-xs text-slate-400">{{ $row['status'] }} · Cash sales Rp{{ number_format($row['cash_sales'], 0, ',', '.') }}</p>
                        </div>
                        <p class="font-bold text-slate-900">Rp{{ number_format($row['expected'], 0, ',', '.') }}</p>
                    </div>
                @empty
                    <p class="py-4 text-sm text-slate-400">Tidak ada shift aktif.</p>
                @endforelse
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="font-bold text-slate-900">Payment Breakdown</h2>
            <div class="mt-5 space-y-4">
                @foreach ($paymentBreakdown as $type => $payment)
                    <div>
                        <div class="flex justify-between text-sm"><span class="font-bold text-slate-700">{{ $type }}</span><span class="text-slate-400">{{ $payment['percent'] }}% · Rp{{ number_format($payment['amount'], 0, ',', '.') }}</span></div>
                        <div class="mt-2 h-2 rounded-full bg-slate-100"><div class="h-2 rounded-full bg-teal-500" style="width: {{ min(100, (float) $payment['percent']) }}%"></div></div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>

    <div class="grid gap-5 lg:grid-cols-2">
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="font-bold text-slate-900">Top Products</h2>
            <div class="mt-3 divide-y divide-slate-100">
                @forelse ($topProducts as $index => $product)
                    <div class="flex items-center justify-between py-3"><span class="text-slate-700">{{ $index + 1 }}. {{ $product->name }}</span><span class="text-sm font-bold text-teal-600">{{ $product->quantity }} terjual</span></div>
                @empty
                    <p class="py-4 text-sm text-slate-400">Belum ada penjualan.</p>
                @endforelse
            </div>
        </section>
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex justify-between"><h2 class="font-bold text-slate-900">Low Stock</h2><a href="{{ route('admin.products.index') }}" class="text-xs font-bold text-teal-600">Kelola</a></div>
            <div class="mt-3 divide-y divide-slate-100">
                @forelse ($lowStock as $product)
                    <div class="flex items-center justify-between py-3"><span class="text-slate-700">{{ $product->name }}</span><span class="rounded-full bg-rose-50 px-2.5 py-1 text-xs font-bold text-rose-600">{{ $product->stock }} tersisa</span></div>
                @empty
                    <p class="py-4 text-sm text-slate-400">Semua stok aman.</p>
                @endforelse
            </div>
        </section>
    </div>

    <div class="grid gap-5 xl:grid-cols-2">
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="font-bold text-slate-900">Recent Transactions</h2>
            <div class="mt-3 overflow-x-auto"><table class="w-full text-left text-sm"><thead class="text-xs uppercase tracking-wider text-slate-400"><tr><th class="py-2">Order</th><th>Staff</th><th>Payment</th><th class="text-right">Total</th></tr></thead><tbody class="divide-y divide-slate-100">
                @forelse ($recentOrders as $order)
                    <tr><td class="py-3 font-semibold text-slate-700">#{{ $order->order_number ?? $order->id }}</td><td class="text-slate-500">{{ $order->user?->name ?? '—' }}</td><td class="text-slate-500">{{ $order->payment_type }}</td><td class="text-right font-bold text-slate-900">Rp{{ number_format((float) $order->total_amount, 0, ',', '.') }}</td></tr>
                @empty
                    <tr><td colspan="4" class="py-5 text-slate-400">Belum ada transaksi.</td></tr>
                @endforelse
            </tbody></table></div>
        </section>
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="font-bold text-slate-900">Recent Activity</h2>
            <div class="mt-3 divide-y divide-slate-100">
                @forelse ($activities as $activity)
                    <div class="py-3"><p class="text-sm text-slate-700">{{ $activity->action }}</p><p class="mt-1 text-xs text-slate-400">{{ $activity->user?->name ?? 'System' }} · {{ $activity->created_at?->timezone('Asia/Jakarta')->format('H:i') }}</p></div>
                @empty
                    <p class="py-4 text-sm text-slate-400">Belum ada aktivitas.</p>
                @endforelse
            </div>
        </section>
    </div>
</div>
