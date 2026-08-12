<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Helvetica POS — Kasir</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak] { display: none !important; }</style>
    @livewireStyles
</head>
<body class="bg-slate-100 h-screen overflow-hidden">

<div x-data="posApp()" x-init="init()" class="h-screen flex flex-col">
    @livewire('pos-realtime', ['shiftId' => $activeShift?->id])

    {{-- Top bar --}}
    <header class="bg-white border-b px-6 py-3 flex items-center justify-between shrink-0">
        <h1 class="text-lg font-bold text-slate-800">Helvetica POS <span class="text-slate-400 font-normal">/ Kasir</span></h1>
        <div class="flex items-center gap-3">
            <span class="text-sm text-slate-500">{{ Auth::user()->name }} ({{ ucfirst(Auth::user()->role) }})</span>
            <button @click="transactionsModalOpen = true"
                    class="text-sm bg-slate-100 text-slate-600 hover:bg-slate-200 rounded-lg px-3 py-1.5 font-medium">
                Riwayat Transaksi
            </button>
            <button @click="closeShiftModalOpen = true"
                    class="text-sm bg-red-50 text-red-600 hover:bg-red-100 rounded-lg px-3 py-1.5 font-medium">
                Tutup Shift
            </button>
            <form id="logoutForm" method="POST" action="{{ route('pos.logout') }}">
                @csrf
                <button class="text-sm bg-slate-100 hover:bg-slate-200 rounded-lg px-3 py-1.5 font-medium">Keluar</button>
            </form>
        </div>
    </header>

    <div class="flex-1 flex overflow-hidden">

        {{-- LEFT: Menu --}}
        <main class="flex-1 flex flex-col overflow-hidden p-6">
            {{-- Category tabs --}}
            <div class="flex gap-2 mb-4 overflow-x-auto pb-1">
                <button @click="activeCategory = 'all'"
                        :class="activeCategory === 'all' ? 'bg-slate-800 text-white' : 'bg-white text-slate-600 hover:bg-slate-200'"
                        class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition">
                    Semua
                </button>
                <template x-for="category in categories" :key="category.id">
                    <button @click="activeCategory = category.id"
                            :class="activeCategory === category.id ? 'bg-slate-800 text-white' : 'bg-white text-slate-600 hover:bg-slate-200'"
                            class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition">
                        <span x-text="category.name"></span>
                    </button>
                </template>
            </div>

            {{-- Product grid --}}
            <div class="flex-1 overflow-y-auto">
                <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-4">
                    <template x-for="product in filteredProducts" :key="product.id">
                        <button @click="addToCart(product)"
                                :disabled="product.stock <= 0"
                                class="bg-white rounded-xl shadow-sm hover:shadow-md hover:-translate-y-0.5 transition overflow-hidden text-left disabled:cursor-not-allowed disabled:opacity-50">
                            <div class="h-28 bg-slate-200 flex items-center justify-center text-slate-400 text-sm">
                                <template x-if="product.image">
                                    <img :src="product.image" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!product.image">
                                    <span>No Image</span>
                                </template>
                            </div>
                            <div class="p-3">
                                <p class="font-semibold text-slate-800 text-sm truncate" x-text="product.name"></p>
                                <p class="text-emerald-600 font-bold text-sm mt-1" x-text="formatRupiah(product.price)"></p>
                                <p class="text-xs mt-1" :class="product.stock <= product.low_stock_threshold ? 'text-amber-600 font-semibold' : 'text-slate-400'" x-text="'Stok: ' + product.stock"></p>
                            </div>
                        </button>
                    </template>
                </div>
            </div>
        </main>

        {{-- RIGHT: Cart --}}
        <aside class="w-[380px] bg-white border-l flex flex-col shrink-0">
            <div class="px-5 py-4 border-b">
                <h2 class="font-bold text-slate-800">Keranjang</h2>
            </div>

            <div class="flex-1 overflow-y-auto px-5 py-3 space-y-3">
                <template x-if="cart.length === 0">
                    <p class="text-sm text-slate-400 text-center mt-10">Belum ada item.</p>
                </template>

                <template x-for="(item, index) in cart" :key="item.product_id">
                    <div class="flex items-start justify-between gap-2 pb-3 border-b border-slate-100">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-800 truncate" x-text="item.name"></p>
                            <p class="text-xs text-slate-400" x-text="formatRupiah(item.price) + ' / item'"></p>
                            <div class="flex items-center gap-2 mt-2">
                                <button @click="decQty(index)" class="w-6 h-6 rounded-full bg-slate-100 hover:bg-slate-200 text-sm font-bold">−</button>
                                <span class="text-sm font-semibold w-5 text-center" x-text="item.quantity"></span>
                                <button @click="incQty(index)" :disabled="item.quantity >= item.stock" class="w-6 h-6 rounded-full bg-slate-100 hover:bg-slate-200 text-sm font-bold disabled:opacity-40">+</button>
                                <button @click="removeItem(index)" class="ml-2 text-xs text-red-400 hover:text-red-600">Hapus</button>
                            </div>
                        </div>
                        <p class="text-sm font-semibold text-slate-700 whitespace-nowrap" x-text="formatRupiah(item.price * item.quantity)"></p>
                    </div>
                </template>
            </div>

            {{-- Summary + payment --}}
            <div class="border-t px-5 py-4 space-y-3 bg-slate-50">
                <div class="space-y-1 text-sm">
                    <div class="flex justify-between text-slate-600">
                        <span>Total Belanja (nett)</span>
                        <span x-text="formatRupiah(totalBelanja)"></span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Pajak (PB1)</span>
                        <span x-text="calculating ? '…' : formatRupiah(taxAmount)"></span>
                    </div>
                    <template x-if="paymentType === 'QRIS' && gatewayFeeAmount > 0">
                        <div class="flex justify-between text-slate-600">
                            <span>Biaya QRIS</span>
                            <span x-text="calculating ? '…' : formatRupiah(gatewayFeeAmount)"></span>
                        </div>
                    </template>
                    <div class="flex justify-between text-slate-600">
                        <span>Pembulatan</span>
                        <span x-text="formatRupiah(roundingAdjustment)"></span>
                    </div>
                    <div class="flex justify-between font-bold text-slate-800 text-base pt-1 border-t">
                        <span>Total Bayar</span>
                        <span x-text="calculating ? '…' : formatRupiah(totalAmount)"></span>
                    </div>
                </div>

                <template x-if="calcError">
                    <p class="text-xs text-red-500" x-text="calcError"></p>
                </template>

                <div class="grid grid-cols-2 gap-2">
                    <button @click="paymentType = 'CASH'"
                            :class="paymentType === 'CASH' ? 'bg-slate-800 text-white' : 'bg-white text-slate-600 border'"
                            class="rounded-lg py-2 text-sm font-semibold">CASH</button>
                    <button @click="paymentType = 'QRIS'"
                            :class="paymentType === 'QRIS' ? 'bg-slate-800 text-white' : 'bg-white text-slate-600 border'"
                            class="rounded-lg py-2 text-sm font-semibold">QRIS</button>
                </div>

                <template x-if="paymentType === 'CASH'">
                    <div>
                        <label class="text-xs text-slate-500">Uang Dibayar</label>
                        <input type="number" x-model.number="cashGiven" placeholder="0"
                               class="w-full mt-1 rounded-lg border-slate-300 text-sm px-3 py-2">
                        <div class="flex justify-between text-sm mt-2 font-medium"
                             :class="changeAmount < 0 ? 'text-red-500' : 'text-emerald-600'">
                            <span>Kembalian</span>
                            <span x-text="formatRupiah(Math.max(changeAmount, 0))"></span>
                        </div>
                    </div>
                </template>

                <template x-if="errorMessage">
                    <p class="text-xs text-red-500" x-text="errorMessage"></p>
                </template>

                <button @click="submitOrder()"
                        :disabled="!canCheckout || submitting"
                        class="w-full bg-emerald-600 hover:bg-emerald-500 disabled:opacity-40 disabled:cursor-not-allowed text-white rounded-xl py-3 font-bold">
                    <span x-show="!submitting">Proses Transaksi</span>
                    <span x-show="submitting">Memproses...</span>
                </button>
            </div>
        </aside>
    </div>

    {{-- Open-shift modal (blocks POS usage until a shift is opened) --}}
    <div x-show="openShiftModalOpen" x-cloak
         class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-white rounded-2xl p-6 w-full max-w-sm">
            <h3 class="font-bold text-lg mb-1">Buka Shift</h3>
            <p class="text-sm text-slate-500 mb-4">Masukkan modal kas awal untuk memulai shift. Nominal boleh lebih kecil dari shift sebelumnya.</p>
            <label class="text-xs text-slate-500">Modal Awal (Rp)</label>
            <input type="number" x-model.number="initialCash" placeholder="0"
                   class="w-full mt-1 mb-4 rounded-lg border-slate-300 px-3 py-2">
            <button @click="openShift()" :disabled="submitting"
                    class="w-full bg-slate-800 hover:bg-slate-700 text-white rounded-xl py-3 font-bold disabled:opacity-40">
                Mulai Shift
            </button>
        </div>
    </div>

    {{-- Close-shift modal --}}
    <div x-show="closeShiftModalOpen" x-cloak
         class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-white rounded-2xl p-6 w-full max-w-sm">
            <h3 class="font-bold text-lg mb-1">Tutup Shift</h3>
            <p class="text-sm text-slate-500 mb-4">Hitung uang fisik di laci kasir.</p>
            <label class="text-xs text-slate-500">Uang Fisik Dihitung (Rp)</label>
            <input type="number" x-model.number="actualCash" placeholder="0"
                   class="w-full mt-1 mb-4 rounded-lg border-slate-300 px-3 py-2">
            <div class="flex gap-2">
                <button @click="closeShiftModalOpen = false"
                        class="flex-1 bg-slate-100 hover:bg-slate-200 rounded-xl py-3 font-semibold">Batal</button>
                <button @click="closeShift()" :disabled="submitting"
                        class="flex-1 bg-red-600 hover:bg-red-500 text-white rounded-xl py-3 font-bold disabled:opacity-40">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    {{-- Transaction history modal — pick an order to request cancellation for --}}
    <div x-show="transactionsModalOpen" x-cloak
         class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-white rounded-2xl p-6 w-full max-w-md max-h-[80vh] flex flex-col">
            <div class="flex items-center justify-between mb-1">
                <h3 class="font-bold text-lg">Riwayat Transaksi Shift Ini</h3>
                <button @click="transactionsModalOpen = false" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>
            <p class="text-sm text-slate-500 mb-4">Pembatalan perlu disetujui admin sebelum transaksi benar-benar batal.</p>

            <div class="flex-1 overflow-y-auto space-y-2">
                <template x-if="recentOrders.length === 0">
                    <p class="text-sm text-slate-400 text-center mt-6">Belum ada transaksi pada shift ini.</p>
                </template>
                <template x-for="order in recentOrders" :key="order.id">
                    <div class="border border-slate-100 rounded-xl p-3 flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-slate-800 truncate" x-text="order.order_number"></p>
                            <p class="text-xs text-slate-400" x-text="order.created_at + ' · ' + formatRupiah(order.total_amount)"></p>
                        </div>
                        <template x-if="order.status === 'cancelled'">
                            <span class="text-xs font-semibold text-slate-400 whitespace-nowrap">Dibatalkan</span>
                        </template>
                        <template x-if="order.has_pending_cancellation">
                            <span class="text-xs font-semibold text-amber-600 whitespace-nowrap">Menunggu Admin</span>
                        </template>
                        <template x-if="order.can_request_cancellation">
                            <button @click="openCancellationForm(order)"
                                    class="text-xs font-semibold text-red-600 hover:text-red-700 whitespace-nowrap shrink-0">
                                Ajukan Pembatalan
                            </button>
                        </template>
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- Cancellation request form modal --}}
    <div x-show="cancellationModalOpen" x-cloak
         class="fixed inset-0 bg-black/60 flex items-center justify-center z-[60]">
        <div class="bg-white rounded-2xl p-6 w-full max-w-sm">
            <h3 class="font-bold text-lg mb-1">Ajukan Pembatalan</h3>
            <p class="text-sm text-slate-500 mb-4">
                Transaksi <span class="font-semibold" x-text="cancellingOrder?.order_number"></span> akan tetap
                berstatus paid sampai admin menyetujui pengajuan ini.
            </p>
            <label class="text-xs text-slate-500">Alasan Pembatalan</label>
            <textarea x-model="cancellationReason" rows="3" placeholder="Contoh: Salah input menu, pelanggan batal."
                      class="w-full mt-1 mb-1 rounded-lg border-slate-300 px-3 py-2 text-sm"></textarea>
            <p class="text-xs text-red-500 mb-3" x-show="cancellationError" x-text="cancellationError"></p>
            <div class="flex gap-2">
                <button @click="cancellationModalOpen = false"
                        class="flex-1 bg-slate-100 hover:bg-slate-200 rounded-xl py-3 font-semibold">Batal</button>
                <button @click="requestCancellation()" :disabled="submitting"
                        class="flex-1 bg-red-600 hover:bg-red-500 text-white rounded-xl py-3 font-bold disabled:opacity-40">
                    Kirim Pengajuan
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function posApp() {
        return {
            categories: @json($categories),
            activeCategory: 'all',
            cart: [],
            paymentType: 'CASH',
            cashGiven: 0,
            submitting: false,
            errorMessage: '',

            // Totals are always fetched from the server (same
            // FinancialCalculationService used when the order is actually
            // saved) so the cashier never sees a number that doesn't match
            // what gets charged and printed on the receipt.
            calculating: false,
            calcError: '',
            calcTimer: null,
            serverTotals: {
                subtotal: 0,
                tax_amount: 0,
                gateway_fee_amount: 0,
                rounding_adjustment: 0,
                total_amount: 0,
            },

            hasActiveShift: @json((bool) $activeShift),
            openShiftModalOpen: false,
            closeShiftModalOpen: false,
            initialCash: 0,
            actualCash: 0,

            recentOrders: @json($recentOrders),
            transactionsModalOpen: false,
            cancellationModalOpen: false,
            cancellingOrder: null,
            cancellationReason: '',
            cancellationError: '',

            init() {
                if (!this.hasActiveShift) {
                    this.openShiftModalOpen = true;
                }

                window.addEventListener('pos-data-refreshed', (event) => {
                    const categories = event.detail?.categories;
                    if (!categories) return;

                    this.categories = categories;
                    const currentProducts = new Map(this.allProducts.map(product => [product.id, product]));
                    this.cart = this.cart
                        .map(item => ({ ...item, stock: currentProducts.get(item.product_id)?.stock ?? 0 }))
                        .filter(item => item.stock > 0)
                        .map(item => ({ ...item, quantity: Math.min(item.quantity, item.stock) }));
                });

                // Re-fetch totals from the server whenever the cart contents
                // or payment type change (debounced so rapid +/- clicks
                // don't spam the endpoint).
                this.$watch('cart', () => this.scheduleRecalculate());
                this.$watch('paymentType', () => this.scheduleRecalculate());
            },

            get allProducts() {
                return this.categories.flatMap(c => c.products);
            },

            get filteredProducts() {
                if (this.activeCategory === 'all') return this.allProducts;
                const category = this.categories.find(c => c.id === this.activeCategory);
                return category ? category.products : [];
            },

            addToCart(product) {
                const existing = this.cart.find(i => i.product_id === product.id);
                if (existing) {
                    if (existing.quantity >= product.stock) return;
                    existing.quantity++;
                } else {
                    this.cart.push({
                        product_id: product.id,
                        name: product.name,
                        price: parseFloat(product.price),
                        stock: product.stock,
                        quantity: 1,
                    });
                }
            },

            incQty(index) {
                if (this.cart[index].quantity < this.cart[index].stock) {
                    this.cart[index].quantity++;
                }
            },

            decQty(index) {
                this.cart[index].quantity--;
                if (this.cart[index].quantity <= 0) this.removeItem(index);
            },

            removeItem(index) {
                this.cart.splice(index, 1);
            },

            get subtotal() {
                return this.cart.reduce((sum, i) => sum + i.price * i.quantity, 0);
            },

            get totalBelanja() {
                return Math.round(this.subtotal * 100) / 100;
            },

            get taxAmount() {
                return this.serverTotals.tax_amount;
            },

            get gatewayFeeAmount() {
                return this.serverTotals.gateway_fee_amount;
            },

            get roundingAdjustment() {
                return this.serverTotals.rounding_adjustment;
            },

            get totalAmount() {
                return this.serverTotals.total_amount;
            },

            get changeAmount() {
                return (this.cashGiven || 0) - this.totalAmount;
            },

            scheduleRecalculate() {
                clearTimeout(this.calcTimer);

                if (this.cart.length === 0) {
                    this.serverTotals = { subtotal: 0, tax_amount: 0, gateway_fee_amount: 0, rounding_adjustment: 0, total_amount: 0 };
                    this.calcError = '';
                    this.calculating = false;
                    return;
                }

                this.calcTimer = setTimeout(() => this.recalculate(), 250);
            },

            async recalculate() {
                this.calculating = true;
                try {
                    const res = await fetch('{{ route('orders.calculate') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            items: this.cart.map(i => ({ product_id: i.product_id, quantity: i.quantity })),
                            payment_type: this.paymentType,
                        }),
                    });

                    const data = await res.json();

                    if (!res.ok) {
                        this.calcError = data.message || 'Gagal menghitung total.';
                        return;
                    }

                    this.calcError = '';
                    this.serverTotals = data;
                } catch (e) {
                    this.calcError = 'Gagal menghitung total (jaringan).';
                } finally {
                    this.calculating = false;
                }
            },

            get canCheckout() {
                if (this.cart.length === 0) return false;
                if (this.calculating) return false;
                if (this.paymentType === 'CASH' && this.changeAmount < 0) return false;
                return true;
            },

            formatRupiah(value) {
                return 'Rp' + Math.round(value || 0).toLocaleString('id-ID');
            },

            async submitOrder() {
                this.errorMessage = '';
                this.submitting = true;
                try {
                    const res = await fetch('{{ route('orders.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            items: this.cart.map(i => ({ product_id: i.product_id, quantity: i.quantity })),
                            payment_type: this.paymentType,
                            cash_given: this.paymentType === 'CASH' ? this.cashGiven : null,
                        }),
                    });

                    const data = await res.json();

                    if (!res.ok) {
                        this.errorMessage = data.message || 'Transaksi gagal.';
                        return;
                    }

                    const receiptUrl = '{{ url('/orders') }}/' + data.order.id + '/receipt?print=1';
                    const receiptWindow = window.open(receiptUrl, '_blank');

                    if (!receiptWindow) {
                        this.errorMessage = 'Struk berhasil dibuat, tetapi popup diblokir browser.';
                    }

                    alert(
                        'Transaksi berhasil: ' + data.order.order_number +
                        (data.change !== null ? '\nKembalian: ' + this.formatRupiah(data.change) : '')
                    );

                    this.cart = [];
                    this.cashGiven = 0;
                    this.paymentType = 'CASH';
                } catch (e) {
                    this.errorMessage = 'Terjadi kesalahan jaringan.';
                } finally {
                    this.submitting = false;
                }
            },

            async openShift() {
                this.submitting = true;
                try {
                    const res = await fetch('{{ route('shifts.open') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ initial_cash: this.initialCash }),
                    });
                    const data = await res.json();
                    if (!res.ok) {
                        alert(data.message);
                        return;
                    }
                    this.hasActiveShift = true;
                    this.openShiftModalOpen = false;
                } finally {
                    this.submitting = false;
                }
            },

            async closeShift() {
                this.submitting = true;
                try {
                    const res = await fetch('{{ route('shifts.close') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ actual_cash: this.actualCash }),
                    });
                    const data = await res.json();
                    if (!res.ok) {
                        alert(data.message);
                        return;
                    }
                    alert(
                        'Shift ditutup.\nExpected: ' + this.formatRupiah(data.shift.expected_cash) +
                        '\nActual: ' + this.formatRupiah(data.shift.actual_cash) +
                        '\nVariance: ' + this.formatRupiah(data.shift.variance)
                    );
                    document.getElementById('logoutForm').submit(); // shift closed -> log the cashier out instead of re-rendering the POS page
                } finally {
                    this.submitting = false;
                }
            },

            openCancellationForm(order) {
                this.cancellingOrder = order;
                this.cancellationReason = '';
                this.cancellationError = '';
                this.transactionsModalOpen = false;
                this.cancellationModalOpen = true;
            },

            async requestCancellation() {
                if (!this.cancellingOrder) return;
                this.cancellationError = '';
                this.submitting = true;
                try {
                    const res = await fetch('{{ url('/orders') }}/' + this.cancellingOrder.id + '/cancellation-requests', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ reason: this.cancellationReason }),
                    });
                    const data = await res.json();
                    if (!res.ok) {
                        this.cancellationError = data.message || 'Pengajuan gagal, coba lagi.';
                        return;
                    }
                    // Reflect the new pending state locally so the history
                    // list updates without a full page reload.
                    const order = this.recentOrders.find(o => o.id === this.cancellingOrder.id);
                    if (order) {
                        order.can_request_cancellation = false;
                        order.has_pending_cancellation = true;
                    }
                    this.cancellationModalOpen = false;
                    alert('Pengajuan pembatalan terkirim. Menunggu persetujuan admin.');
                } catch (e) {
                    this.cancellationError = 'Terjadi kesalahan jaringan, coba lagi.';
                } finally {
                    this.submitting = false;
                }
            },
        };
    }
</script>
@livewireScripts
</body>
</html>
