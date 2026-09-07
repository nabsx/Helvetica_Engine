<x-admin-layout>
    <div class="space-y-8" data-purpose="monitoring-dashboard">
        {{-- Page Title & Quick Description --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">Cash Monitoring &amp; Approval</h1>
                <p class="text-sm text-slate-500 mt-1">Pemantauan kas laci tunai waktu nyata dan persetujuan penutupan shift kasir.</p>
            </div>
            <div class="flex items-center space-x-2.5">
                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center space-x-2 px-3.5 py-2 text-xs font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-sm shadow-emerald-500/20 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
                    <span>Kembali ke Dashboard</span>
                </a>
            </div>
        </div>

        @if(session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('status') }}</div>
        @endif

        {{-- KPI Cards --}}
        <section aria-label="Metrik Ringkasan Kas" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-2xs flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Kas Fisik Terkini</span>
                    <span class="p-2 rounded-xl bg-emerald-50 text-emerald-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
                    </span>
                </div>
                <div class="mt-4">
                    <p class="text-2xl font-bold text-slate-900 tracking-tight">Rp {{ number_format($summary->totalPhysical, 0, ',', '.') }}</p>
                    <div class="flex items-center text-xs text-slate-500 mt-1">
                        <span>Estimasi Sistem: <strong class="text-slate-700">Rp {{ number_format($summary->totalExpected, 0, ',', '.') }}</strong></span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-5 border {{ $summary->pendingCount > 0 ? 'border-amber-200 bg-amber-50/20' : 'border-slate-200' }} shadow-2xs flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider {{ $summary->pendingCount > 0 ? 'text-amber-700' : 'text-slate-500' }}">Shift Butuh Approval</span>
                    <span class="p-2 rounded-xl {{ $summary->pendingCount > 0 ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-500' }} font-bold text-xs flex items-center justify-center min-w-[28px]">{{ $summary->pendingCount }}</span>
                </div>
                <div class="mt-4">
                    <p class="text-2xl font-bold {{ $summary->pendingCount > 0 ? 'text-amber-900' : 'text-slate-900' }} tracking-tight">{{ $summary->pendingCount }} Shift Menunggu</p>
                    @php $firstPending = $rows->firstWhere(fn ($row) => $row->shift->status === 'pending_close'); @endphp
                    <div class="flex items-center space-x-1.5 text-xs {{ $summary->pendingCount > 0 ? 'text-amber-700' : 'text-slate-400' }} font-medium mt-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
                        <span>{{ $firstPending ? 'Kasir: '.$firstPending->shift->user->name.' (Shift #'.$firstPending->shift->id.')' : 'Tidak ada shift menunggu' }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-5 border {{ $summary->totalVariance < 0 ? 'border-rose-200 bg-rose-50/20' : 'border-slate-200' }} shadow-2xs flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider {{ $summary->totalVariance < 0 ? 'text-rose-700' : 'text-slate-500' }}">Total Selisih (Variance)</span>
                    <span class="p-2 rounded-xl {{ $summary->totalVariance < 0 ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-500' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
                    </span>
                </div>
                <div class="mt-4">
                    <p class="text-2xl font-bold {{ $summary->totalVariance < 0 ? 'text-rose-600' : ($summary->totalVariance > 0 ? 'text-emerald-600' : 'text-slate-900') }} tracking-tight">{{ $summary->totalVariance < 0 ? '-' : ($summary->totalVariance > 0 ? '+' : '') }}Rp {{ number_format(abs($summary->totalVariance), 0, ',', '.') }}</p>
                    <div class="flex items-center text-xs {{ $summary->totalVariance < 0 ? 'text-rose-700' : 'text-slate-500' }} mt-1 font-medium">
                        <span>Status: {{ $summary->totalVariance < 0 ? 'Defisit Kas (Minus)' : ($summary->totalVariance > 0 ? 'Surplus Kas (Plus)' : 'Seimbang') }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-2xs flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Penjualan Tunai Shift</span>
                    <span class="p-2 rounded-xl bg-blue-50 text-blue-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
                    </span>
                </div>
                <div class="mt-4">
                    <p class="text-2xl font-bold text-slate-900 tracking-tight">Rp {{ number_format($summary->totalCashSales, 0, ',', '.') }}</p>
                    <div class="flex items-center justify-between text-xs text-slate-500 mt-1">
                        <span>Non-Tunai: Rp {{ number_format($summary->totalNonCashSales, 0, ',', '.') }}</span>
                        <span class="text-emerald-600 font-medium">{{ $summary->totalTxCount }} Transaksi</span>
                    </div>
                </div>
            </div>
        </section>

        {{-- Master / Detail --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            {{-- LEFT COLUMN: Shifts Monitoring List --}}
            <div class="lg:col-span-7 space-y-4">
                <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                    <div class="p-5 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white">
                        <div>
                            <div class="flex items-center space-x-2">
                                <h2 class="text-base font-bold text-slate-900">Daftar Shift Kasir</h2>
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">{{ $rows->count() }} Aktif</span>
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">Shift kasir yang sedang berjalan atau menunggu verifikasi supervisor</p>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="border-b border-slate-200 bg-slate-50/75 text-[11px] font-semibold tracking-wider text-slate-500 uppercase">
                                    <th class="py-3 px-4">Kasir &amp; Shift</th>
                                    <th class="py-3 px-3">Status</th>
                                    <th class="py-3 px-3 text-right">Kas Awal</th>
                                    <th class="py-3 px-3 text-right">Ekspektasi</th>
                                    <th class="py-3 px-3">Aktivitas</th>
                                    <th class="py-3 px-4 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($rows as $row)
                                    @php $isSelected = $selected && $selected->shift->id === $row->shift->id; @endphp
                                    <tr class="hover:bg-slate-50/80 transition-colors {{ $isSelected ? 'bg-emerald-50/15 border-l-4 border-l-emerald-600' : '' }}">
                                        <td class="py-4 px-4 whitespace-nowrap">
                                            <div class="flex items-center space-x-3">
                                                <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-700 font-bold flex items-center justify-center text-xs border border-emerald-200">
                                                    {{ strtoupper(substr($row->shift->user->name, 0, 2)) }}
                                                </div>
                                                <div>
                                                    <div class="font-semibold text-slate-900 text-sm flex items-center space-x-1.5">
                                                        <span>{{ $row->shift->user->name }}</span>
                                                        <span class="text-[11px] font-normal text-slate-500">&bull; Shift #{{ $row->shift->id }}</span>
                                                    </div>
                                                    <span class="text-xs text-slate-400">Dibuka {{ $row->shift->start_time->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-4 px-3 whitespace-nowrap">
                                            @if($row->shift->status === 'pending_close')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-1.5 animate-pulse"></span>pending_close
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>open
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-4 px-3 whitespace-nowrap text-right font-medium text-slate-700">Rp {{ number_format((float) $row->shift->opening_cash, 0, ',', '.') }}</td>
                                        <td class="py-4 px-3 whitespace-nowrap text-right font-semibold text-slate-900">Rp {{ number_format($row->expected, 0, ',', '.') }}</td>
                                        <td class="py-4 px-3 whitespace-nowrap text-xs text-slate-500">
                                            <div class="flex items-center space-x-1">
                                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
                                                <span>{{ $row->lastActivity }}</span>
                                            </div>
                                        </td>
                                        <td class="py-4 px-4 whitespace-nowrap text-right">
                                            <div class="flex items-center justify-end space-x-2">
                                                <a href="{{ route('admin.shifts.index', ['shift' => $row->shift->id]) }}" class="text-emerald-700 hover:text-emerald-800 text-xs font-semibold px-2.5 py-1 rounded-md hover:bg-emerald-50 transition underline-offset-2 hover:underline">Detail</a>
                                                @if($row->shift->status === 'pending_close')
                                                    <form method="POST" action="{{ route('admin.shifts.approve', $row->shift) }}" onsubmit="return confirm('Approve shift ini?')">
                                                        @csrf
                                                        <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-medium transition shadow-xs shadow-emerald-500/20">Approve</button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td class="py-8 px-4 text-center text-sm text-slate-500" colspan="6">Tidak ada shift aktif.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="px-5 py-3.5 bg-slate-50/60 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                        <span>Menampilkan {{ $rows->count() }} dari {{ $rows->count() }} shift kasir aktif</span>
                        <span class="inline-flex items-center space-x-1 text-slate-400">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
                            <span>Otomatis sinkron dengan kasir tablet</span>
                        </span>
                    </div>
                </div>
            </div>

            {{-- RIGHT COLUMN: Detailed Shift Inspection & Approval Drawer --}}
            <div class="lg:col-span-5 space-y-6">
                @if($selected)
                    @php $shift = $selected->shift; @endphp
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="p-6 border-b border-slate-100 bg-white">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-[11px] font-mono font-semibold">SHIFT-{{ $shift->id }}</span>
                                    @if($shift->status === 'pending_close')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-100 text-amber-800">PENDING CLOSE</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-100 text-emerald-800">OPEN</span>
                                    @endif
                                </div>
                                <span class="text-xs text-slate-400">Audit Mode</span>
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 mt-2">Shift {{ $shift->id }} &bull; {{ $shift->user->name }}</h3>
                            <p class="text-xs text-slate-500 mt-1">Dibuka oleh <strong class="text-slate-700">{{ $shift->opener?->name ?? $shift->user->name }}</strong> pada {{ $shift->start_time->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</p>
                        </div>
                        <div class="p-6 space-y-4">
                            <div class="grid grid-cols-2 gap-3">
                                <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200/80">
                                    <span class="text-xs font-medium text-slate-500 block">Kas Awal (Opening)</span>
                                    <span class="text-lg font-bold text-slate-900 mt-1 block">Rp {{ number_format((float) $shift->opening_cash, 0, ',', '.') }}</span>
                                </div>
                                <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200/80">
                                    <span class="text-xs font-medium text-slate-500 block">Ekspektasi Sistem</span>
                                    <span class="text-lg font-bold text-slate-900 mt-1 block">Rp {{ number_format($selected->expected, 0, ',', '.') }}</span>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200/80">
                                    <span class="text-xs font-medium text-slate-500 block">Fisik Dihitung (Closing)</span>
                                    <span class="text-lg font-bold text-slate-900 mt-1 block">{{ $shift->closing_cash === null ? '-' : 'Rp '.number_format((float) $shift->closing_cash, 0, ',', '.') }}</span>
                                </div>
                                <div class="p-3.5 {{ $selected->difference === null ? 'bg-slate-50 border-slate-200/80' : ($selected->difference < 0 ? 'bg-rose-50/80 border-rose-200' : ($selected->difference > 0 ? 'bg-emerald-50/80 border-emerald-200' : 'bg-slate-50 border-slate-200/80')) }} rounded-xl border">
                                    <span class="text-xs font-semibold {{ $selected->difference === null ? 'text-slate-500' : ($selected->difference < 0 ? 'text-rose-700' : ($selected->difference > 0 ? 'text-emerald-700' : 'text-slate-500')) }} block">Selisih (Difference)</span>
                                    <div class="flex items-baseline space-x-1.5 mt-1">
                                        <span class="text-lg font-bold {{ $selected->difference === null ? 'text-slate-900' : ($selected->difference < 0 ? 'text-rose-600' : ($selected->difference > 0 ? 'text-emerald-600' : 'text-slate-900')) }} block">
                                            {{ $selected->difference === null ? '-' : ($selected->difference < 0 ? '-' : ($selected->difference > 0 ? '+' : '')).'Rp '.number_format(abs($selected->difference), 0, ',', '.') }}
                                        </span>
                                        @if($selected->difference !== null)
                                            <span class="text-[10px] font-semibold {{ $selected->difference < 0 ? 'text-rose-500' : ($selected->difference > 0 ? 'text-emerald-500' : 'text-slate-400') }} uppercase tracking-tight">
                                                {{ $selected->difference < 0 ? 'Defisit' : ($selected->difference > 0 ? 'Surplus' : 'Balance') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 pt-5 border-t border-slate-200">
                                <div class="flex items-center justify-between mb-3">
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700">Cash Movements (Mutasi Kas)</h4>
                                    <span class="text-[11px] text-slate-400">{{ $shift->cashMovements->count() }} Catatan Ekstra</span>
                                </div>
                                @forelse($shift->cashMovements as $movement)
                                    <div class="flex items-center justify-between py-2 border-b border-slate-100 text-xs">
                                        <span class="text-slate-600">{{ strtoupper($movement->category) }} &bull; {{ $movement->description }}</span>
                                        <span class="font-semibold {{ $movement->type === 'in' ? 'text-emerald-600' : 'text-slate-700' }}">{{ $movement->type === 'in' ? '+' : '-' }} Rp {{ number_format((float) $movement->amount, 0, ',', '.') }}</span>
                                    </div>
                                @empty
                                    <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50/50 p-4 text-center">
                                        <div class="w-8 h-8 rounded-full bg-slate-200/70 text-slate-500 mx-auto flex items-center justify-center mb-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
                                        </div>
                                        <p class="text-xs font-medium text-slate-600">Opening cash is shown in the summary and is not a movement record.</p>
                                        <p class="text-[11px] text-slate-400 mt-1">Tidak ada cash drop atau petty cash tambahan pada shift ini.</p>
                                    </div>
                                @endforelse

                                <div class="mt-4 bg-slate-50 p-3 rounded-xl border border-slate-200/60 space-y-2 text-xs">
                                    <div class="flex justify-between text-slate-600"><span>Kas Awal Shift</span><span class="font-medium text-slate-900">Rp {{ number_format((float) $shift->opening_cash, 0, ',', '.') }}</span></div>
                                    <div class="flex justify-between text-slate-600"><span>+ Total Penjualan Tunai</span><span class="font-medium text-emerald-600">+Rp {{ number_format($selected->cashSales, 0, ',', '.') }}</span></div>
                                    <div class="flex justify-between text-slate-600"><span>+ Kas Masuk (Cash In)</span><span class="font-medium text-emerald-600">+Rp {{ number_format($cashDrawer->getCashIn($shift), 0, ',', '.') }}</span></div>
                                    <div class="flex justify-between text-slate-600"><span>- Pengeluaran Kas (Petty Cash)</span><span class="font-medium text-slate-500">Rp {{ number_format($cashDrawer->getCashOut($shift), 0, ',', '.') }}</span></div>
                                    <div class="h-px bg-slate-200"></div>
                                    <div class="flex justify-between font-semibold text-slate-900"><span>= Seharusnya di Laci (Expected)</span><span>Rp {{ number_format($selected->expected, 0, ',', '.') }}</span></div>
                                </div>
                            </div>

                            @if($shift->status === 'pending_close')
                                <div class="pt-5 border-t border-slate-200 flex flex-col sm:flex-row items-center gap-3">
                                    <form method="POST" action="{{ route('admin.shifts.reject', $shift) }}" onsubmit="return confirm('Kembalikan shift ini ke kasir untuk hitung ulang?')" class="w-full sm:w-1/2">
                                        @csrf
                                        <button type="submit" class="w-full py-2.5 px-4 rounded-xl border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-semibold transition">Tolak &amp; Minta Hitung Ulang</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.shifts.approve', $shift) }}" onsubmit="return confirm('Approve shift ini?')" class="w-full sm:w-1/2">
                                        @csrf
                                        <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold transition shadow-sm shadow-emerald-500/20 flex items-center justify-center space-x-1.5">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
                                            <span>Approve &amp; Tutup Shift</span>
                                        </button>
                                    </form>
                                </div>
                            @else
                                <div class="pt-5 border-t border-slate-200">
                                    <p class="text-xs text-slate-500 text-center">Shift masih berjalan &mdash; menunggu kasir menutup shift.</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="p-4 bg-emerald-50/60 rounded-2xl border border-emerald-200/60 flex items-start space-x-3">
                        <div class="text-emerald-600 mt-0.5">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
                        </div>
                        <div>
                            <h5 class="text-xs font-bold text-emerald-900">Standar Prosedur Kasir</h5>
                            <p class="text-[11px] text-emerald-800/80 mt-0.5 leading-relaxed">Selisih di atas Rp 20.000 memerlukan konfirmasi tandatangan supervisor dan kasir terkait sebelum shift ditutup sepenuhnya.</p>
                        </div>
                    </div>
                @else
                    <div class="bg-white rounded-2xl border border-dashed border-slate-200 p-10 text-center">
                        <p class="text-sm font-medium text-slate-500">Tidak ada shift aktif untuk ditampilkan.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-admin-layout>