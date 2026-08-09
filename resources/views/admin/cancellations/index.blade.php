<x-admin-layout title="Pembatalan Transaksi">
    <div class="mb-6">
        <p class="text-sm font-semibold text-emerald-600">Kontrol kasir</p>
        <h1 class="mt-1 text-3xl font-black tracking-tight">Pengajuan pembatalan transaksi</h1>
        <p class="mt-2 text-sm text-slate-500">Kasir hanya bisa mengajukan — transaksi baru benar-benar batal setelah Anda menyetujui di sini.</p>
    </div>

    @if(session('success'))<div class="mb-6 rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="mb-6 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>@endif

    <main class="space-y-8">
        {{-- Pending queue --}}
        <section class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="font-bold">Menunggu persetujuan</h2>
                <p class="mt-1 text-sm text-slate-500">{{ $pending->count() }} pengajuan belum diproses.</p>
            </div>

            @forelse($pending as $request)
                <div class="border-b border-slate-100 px-5 py-5 last:border-b-0">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="font-bold">{{ $request->order->order_number }}</p>
                                <span class="rounded-full bg-amber-50 px-2 py-0.5 text-xs font-bold text-amber-700">Pending</span>
                            </div>
                            <p class="mt-1 text-sm text-slate-500">
                                Rp {{ number_format($request->order->total_amount, 0, ',', '.') }} ·
                                diajukan oleh {{ $request->requestedBy->name }} ·
                                {{ $request->created_at->diffForHumans() }}
                            </p>
                            <p class="mt-2 rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-700">"{{ $request->reason }}"</p>
                        </div>

                        <div class="flex shrink-0 flex-col gap-2 sm:flex-row">
                            <form method="POST" action="{{ route('admin.cancellations.reject', $request) }}"
                                  onsubmit="return confirm('Tolak pengajuan pembatalan ini? Transaksi tetap berstatus paid.')">
                                @csrf
                                <button class="w-full rounded-lg bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-200 sm:w-auto">
                                    Tolak
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.cancellations.approve', $request) }}"
                                  onsubmit="return confirm('Setujui pembatalan {{ $request->order->order_number }}? Stok produk akan dikembalikan otomatis.')">
                                @csrf
                                <button class="w-full rounded-lg bg-red-600 px-4 py-2 text-sm font-bold text-white hover:bg-red-500 sm:w-auto">
                                    Setujui &amp; Batalkan
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <p class="px-5 py-8 text-center text-sm text-slate-400">Tidak ada pengajuan yang menunggu.</p>
            @endforelse
        </section>

        {{-- Recently resolved --}}
        <section class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="font-bold">Riwayat keputusan</h2>
                <p class="mt-1 text-sm text-slate-500">30 pengajuan terakhir yang sudah diproses.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-5 py-3">Order</th>
                            <th class="px-5 py-3">Diajukan Oleh</th>
                            <th class="px-5 py-3">Keputusan</th>
                            <th class="px-5 py-3">Ditinjau Oleh</th>
                            <th class="px-5 py-3">Catatan Admin</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($resolved as $request)
                            <tr>
                                <td class="px-5 py-4 font-semibold">{{ $request->order->order_number }}</td>
                                <td class="px-5 py-4">{{ $request->requestedBy->name }}</td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full px-2 py-0.5 text-xs font-bold {{ $request->status === 'approved' ? 'bg-red-50 text-red-700' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $request->status === 'approved' ? 'Disetujui (Batal)' : 'Ditolak' }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">{{ $request->reviewedBy?->name ?? '—' }}</td>
                                <td class="px-5 py-4 text-slate-500">{{ $request->admin_note ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-8 text-center text-slate-400">Belum ada riwayat.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</x-admin-layout>
