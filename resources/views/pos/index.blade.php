<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Helvetica POS — Kasir</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: "#059669",
                        "primary-hover": "#047857",
                    },
                    fontFamily: {
                        display: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                },
            },
        };
    </script>
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        .material-symbols-rounded { font-variation-settings: 'FILL' 0, 'wght' 500, 'GRAD' 0, 'opsz' 24; line-height: 1; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
    </style>
    @livewireStyles
</head>
<body class="h-full bg-slate-50 text-slate-800 antialiased overflow-hidden select-none">

<div x-data="posApp()" x-init="init()" class="h-full flex flex-col">
    @livewire('pos-realtime', ['shiftId' => $activeShift?->id])

    {{-- Top bar --}}
    <header class="h-16 border-b border-slate-200/80 bg-white/95 backdrop-blur px-5 flex items-center justify-between z-30 shrink-0 shadow-xs">
        <div class="flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-700 flex items-center justify-center text-white font-black text-lg shadow-md shadow-emerald-500/20">H</div>
            <span class="font-extrabold tracking-tight text-base text-slate-900 hidden sm:inline">Helvetica<span class="text-emerald-600">POS</span></span>
        </div>

        <div class="flex-1 max-w-xl mx-6 hidden md:block">
            <div class="relative group">
                <span class="material-symbols-rounded absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-emerald-600 text-xl transition-colors">search</span>
                <input type="text" x-model="searchQuery" placeholder="Cari menu atau SKU produk..."
                       class="w-full pl-10 pr-4 py-2 text-sm rounded-xl border border-slate-200 bg-slate-50/80 text-slate-900 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-600 transition-all shadow-inner">
            </div>
        </div>

        <div class="flex items-center gap-2.5">
            <button @click="transactionsModalOpen = true"
                    class="px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 flex items-center gap-1.5 transition">
                <span class="material-symbols-rounded text-base text-teal-600">receipt_long</span>
                <span class="hidden sm:inline">Riwayat</span>
            </button>

            <div class="h-8 border-l border-slate-200 mx-1"></div>

            <div class="flex items-center gap-2 pl-1 pr-2 py-1 rounded-xl bg-slate-100">
                <div class="w-7 h-7 rounded-lg bg-emerald-600 text-white font-bold flex items-center justify-center text-xs">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <div class="text-left hidden lg:block pr-1">
                    <div class="text-xs font-bold text-slate-800 leading-none">{{ Auth::user()->name }}</div>
                    <div class="text-[10px] text-slate-500 leading-tight">{{ ucfirst(Auth::user()->role) }}</div>
                </div>
            </div>

            <button @click="closeShiftModalOpen = true"
                    class="px-3 py-1.5 rounded-xl border border-rose-200 text-xs font-semibold text-rose-600 bg-rose-50/60 hover:bg-rose-100 flex items-center gap-1 transition ml-1">
                <span class="material-symbols-rounded text-base">lock_clock</span>
                <span class="hidden xl:inline">Tutup Shift</span>
            </button>

            <form id="logoutForm" method="POST" action="{{ route('pos.logout') }}">
                @csrf
                <button class="w-9 h-9 rounded-xl border border-slate-200 bg-white text-slate-500 hover:bg-slate-100 flex items-center justify-center transition" title="Keluar">
                    <span class="material-symbols-rounded text-lg">logout</span>
                </button>
            </form>
        </div>
    </header>

    <div class="flex-1 flex overflow-hidden">

        {{-- LEFT: Menu --}}
        <main class="flex-1 flex flex-col overflow-hidden border-r border-slate-200 bg-slate-50">
            {{-- Category tabs --}}
            <div class="px-6 py-3.5 border-b border-slate-200/80 bg-white/70 backdrop-blur flex items-center justify-between gap-4">
                <div class="flex items-center gap-2 overflow-x-auto pb-1 sm:pb-0">
                    <button @click="activeCategory = 'all'"
                            :class="activeCategory === 'all' ? 'bg-primary text-white shadow-sm shadow-emerald-600/30' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200/90'"
                            class="px-3.5 py-1.5 rounded-xl text-xs font-bold flex items-center gap-1.5 shrink-0 transition whitespace-nowrap">
                        <span>✨ Semua</span>
                        <span :class="activeCategory === 'all' ? 'bg-white/20' : 'bg-slate-100 text-slate-600'" class="px-1.5 py-0.5 rounded-full text-[10px]" x-text="allProducts.length"></span>
                    </button>
                    <template x-for="category in categories" :key="category.id">
                        <button @click="activeCategory = category.id"
                                :class="activeCategory === category.id ? 'bg-primary text-white shadow-sm shadow-emerald-600/30' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200/90'"
                                class="px-3.5 py-1.5 rounded-xl text-xs font-bold flex items-center gap-1.5 shrink-0 transition whitespace-nowrap">
                            <span x-text="categoryEmoji(category.name) + ' ' + category.name"></span>
                            <span :class="activeCategory === category.id ? 'bg-white/20' : 'bg-slate-100 text-slate-600'" class="px-1.5 py-0.5 rounded-full text-[10px]" x-text="category.products.length"></span>
                        </button>
                    </template>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <div class="flex items-center p-0.5 rounded-xl bg-slate-100 border border-slate-200">
                        <button @click="viewMode = 'grid'" :class="viewMode === 'grid' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-400 hover:text-slate-600'" class="p-1 rounded-lg transition">
                            <span class="material-symbols-rounded text-sm block">grid_view</span>
                        </button>
                        <button @click="viewMode = 'list'" :class="viewMode === 'list' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-400 hover:text-slate-600'" class="p-1 rounded-lg transition">
                            <span class="material-symbols-rounded text-sm block">view_list</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Product grid --}}
            <div class="flex-1 overflow-y-auto p-6">
                <template x-if="filteredProducts.length === 0">
                    <p class="text-sm text-slate-400 text-center mt-16">Tidak ada produk yang cocok.</p>
                </template>

                <div x-show="viewMode === 'grid'" class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-4">
                    <template x-for="product in filteredProducts" :key="product.id">
                        <button @click="addToCart(product)"
                                :disabled="product.stock <= 0"
                                class="group relative rounded-2xl bg-white border border-slate-200/80 overflow-hidden shadow-xs hover:shadow-lg hover:-translate-y-1 hover:border-emerald-500/50 transition duration-200 text-left disabled:cursor-not-allowed disabled:opacity-50 flex flex-col justify-between">
                            <div>
                                <div class="relative h-32 w-full bg-slate-100 overflow-hidden flex items-center justify-center">
                                    <template x-if="product.image">
                                        <img :src="product.image" :alt="product.name" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                    </template>
                                    <template x-if="!product.image">
                                        <span class="material-symbols-rounded text-3xl text-slate-300">image</span>
                                    </template>
                                    <span class="absolute top-2 right-2 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-900/75 text-white backdrop-blur flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full" :class="product.stock <= product.low_stock_threshold ? 'bg-amber-400' : 'bg-emerald-400'"></span>
                                        <span x-text="'Stok ' + product.stock"></span>
                                    </span>
                                </div>
                                <div class="p-3">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400" x-text="product.category_name"></span>
                                    <h3 class="font-bold text-sm text-slate-900 leading-snug mt-0.5 truncate" x-text="product.name"></h3>
                                </div>
                            </div>
                            <div class="px-3 pb-3 pt-1 flex items-center justify-between border-t border-dashed border-slate-100">
                                <span class="text-sm font-extrabold text-emerald-600" x-text="formatRupiah(product.price)"></span>
                                <span class="w-8 h-8 rounded-xl bg-emerald-50 text-primary group-hover:bg-primary group-hover:text-white flex items-center justify-center transition shadow-xs">
                                    <span class="material-symbols-rounded text-lg">add</span>
                                </span>
                            </div>
                        </button>
                    </template>
                </div>

                <div x-show="viewMode === 'list'" class="flex flex-col gap-2">
                    <template x-for="product in filteredProducts" :key="product.id">
                        <button @click="addToCart(product)"
                                :disabled="product.stock <= 0"
                                class="group flex items-center gap-3 rounded-2xl bg-white border border-slate-200/80 p-2.5 shadow-xs hover:shadow-md hover:border-emerald-500/50 transition text-left disabled:cursor-not-allowed disabled:opacity-50">
                            <div class="w-14 h-14 rounded-xl bg-slate-100 overflow-hidden flex items-center justify-center shrink-0">
                                <template x-if="product.image">
                                    <img :src="product.image" :alt="product.name" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!product.image">
                                    <span class="material-symbols-rounded text-xl text-slate-300">image</span>
                                </template>
                            </div>
                            <div class="flex-1 min-w-0">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block" x-text="product.category_name"></span>
                                <h3 class="font-bold text-sm text-slate-900 truncate" x-text="product.name"></h3>
                                <span class="text-xs mt-0.5" :class="product.stock <= product.low_stock_threshold ? 'text-amber-600 font-semibold' : 'text-slate-400'" x-text="'Stok: ' + product.stock"></span>
                            </div>
                            <span class="text-sm font-extrabold text-emerald-600 shrink-0" x-text="formatRupiah(product.price)"></span>
                            <span class="w-8 h-8 rounded-xl bg-emerald-50 text-primary group-hover:bg-primary group-hover:text-white flex items-center justify-center transition shadow-xs shrink-0">
                                <span class="material-symbols-rounded text-lg">add</span>
                            </span>
                        </button>
                    </template>
                </div>
            </div>
        </main>

        {{-- RIGHT: Cart --}}
        <aside class="w-[420px] xl:w-[460px] shrink-0 bg-white flex flex-col h-full shadow-2xl z-20">
            <div class="p-4 border-b border-slate-200 shrink-0">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <span class="material-symbols-rounded text-lg">shopping_basket</span>
                        </div>
                        <div>
                            <h2 class="font-bold text-base text-slate-900">Keranjang Pesanan</h2>
                            <p class="text-[11px] text-slate-400">{{ Auth::user()->name }} · belum dibayar</p>
                        </div>
                    </div>
                    <button @click="clearCart()" x-show="cart.length > 0"
                            class="text-xs text-rose-500 hover:text-rose-600 font-semibold px-2 py-1 rounded-lg hover:bg-rose-50 transition flex items-center gap-1">
                        <span class="material-symbols-rounded text-sm">delete_sweep</span> Kosongkan
                    </button>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto px-4 py-3 divide-y divide-slate-100">
                <template x-if="cart.length === 0">
                    <p class="text-sm text-slate-400 text-center mt-10">Belum ada item.</p>
                </template>

                <template x-for="(item, index) in cart" :key="item.product_id">
                    <div class="py-3 flex items-center justify-between gap-3 group">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <span class="text-sm font-bold text-slate-900 leading-tight truncate" x-text="item.name"></span>
                                <span class="text-xs font-bold text-slate-900 whitespace-nowrap" x-text="formatRupiah(item.price * item.quantity)"></span>
                            </div>
                            <div class="text-[11px] text-slate-400 mt-0.5" x-text="formatRupiah(item.price) + ' / item'"></div>
                            <div class="flex items-center gap-1.5 mt-2">
                                <button @click="decQty(index)" class="w-7 h-7 rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-100 flex items-center justify-center transition">
                                    <span class="material-symbols-rounded text-sm">remove</span>
                                </button>
                                <span class="w-6 text-center font-bold text-xs text-slate-800" x-text="item.quantity"></span>
                                <button @click="incQty(index)" :disabled="item.quantity >= item.stock" class="w-7 h-7 rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-100 flex items-center justify-center transition disabled:opacity-40">
                                    <span class="material-symbols-rounded text-sm">add</span>
                                </button>
                                <button @click="removeItem(index)" class="w-7 h-7 rounded-lg text-slate-400 hover:text-rose-500 hover:bg-rose-50 flex items-center justify-center transition ml-0.5" title="Hapus">
                                    <span class="material-symbols-rounded text-base">close</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Summary + payment --}}
            <div class="border-t px-4 py-4 space-y-3 bg-slate-50/70 shrink-0">
                <div class="space-y-1.5 text-xs">
                    <div class="flex items-center justify-between text-slate-600">
                        <span>Total Belanja (nett)</span>
                        <span class="font-medium text-slate-800" x-text="formatRupiah(totalBelanja)"></span>
                    </div>
                    <div class="flex items-center justify-between text-slate-600">
                        <span class="flex items-center gap-1">Pajak (PB1) <span class="material-symbols-rounded text-slate-400 text-xs" title="Dihitung per item sesuai pengaturan pajak produk">info</span></span>
                        <span class="font-medium text-slate-800" x-text="calculating ? '…' : formatRupiah(taxAmount)"></span>
                    </div>
                    <template x-if="paymentType === 'QRIS' && gatewayFeeAmount > 0">
                        <div class="flex items-center justify-between text-slate-600">
                            <span>Biaya QRIS</span>
                            <span class="font-medium text-slate-800" x-text="calculating ? '…' : formatRupiah(gatewayFeeAmount)"></span>
                        </div>
                    </template>
                    <div class="flex items-center justify-between text-slate-600">
                        <span>Pembulatan</span>
                        <span class="font-medium text-slate-800" x-text="formatRupiah(roundingAdjustment)"></span>
                    </div>
                    <div class="pt-2 border-t border-slate-200 flex items-baseline justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-700">Total Bayar</span>
                        <span class="text-2xl font-black text-slate-900 tracking-tight" x-text="calculating ? '…' : formatRupiah(totalAmount)"></span>
                    </div>
                </div>

                <template x-if="calcError">
                    <p class="text-xs text-red-500" x-text="calcError"></p>
                </template>

                <div class="grid grid-cols-2 gap-1.5 pt-1">
                    <button @click="paymentType = 'CASH'"
                            :class="paymentType === 'CASH' ? 'bg-slate-900 text-white' : 'bg-white border border-slate-200 text-slate-700 hover:border-emerald-500'"
                            class="py-2 px-1 rounded-xl font-bold text-xs flex flex-col items-center justify-center gap-1 shadow-sm transition">
                        <span class="material-symbols-rounded text-base">payments</span>
                        <span>CASH</span>
                    </button>
                    <button @click="paymentType = 'QRIS'"
                            :class="paymentType === 'QRIS' ? 'bg-slate-900 text-white' : 'bg-white border border-slate-200 text-slate-700 hover:border-emerald-500'"
                            class="py-2 px-1 rounded-xl font-semibold text-xs flex flex-col items-center justify-center gap-1 transition">
                        <span class="material-symbols-rounded text-base" :class="paymentType === 'QRIS' ? 'text-white' : 'text-emerald-600'">qr_code_scanner</span>
                        <span>QRIS</span>
                    </button>
                </div>

                <template x-if="paymentType === 'CASH'">
                    <div class="bg-white p-2.5 rounded-xl border border-slate-200">
                        <div class="flex items-center justify-between text-xs mb-1">
                            <span class="font-medium text-slate-600">Uang Diterima / Dibayar</span>
                            <div class="flex items-center gap-1">
                                <button @click="cashGiven = Math.ceil(totalAmount)" class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600 hover:bg-emerald-100 hover:text-emerald-700">Pas</button>
                                <button @click="cashGiven = 50000" class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600 hover:bg-emerald-100 hover:text-emerald-700">50k</button>
                                <button @click="cashGiven = 100000" class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600 hover:bg-emerald-100 hover:text-emerald-700">100k</button>
                            </div>
                        </div>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">Rp</span>
                            <input type="number" x-model.number="cashGiven" placeholder="0"
                                   class="w-full pl-9 pr-3 py-1.5 text-sm font-bold rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div class="mt-2 pt-2 border-t border-dashed border-slate-200 flex items-center justify-between text-xs">
                            <span class="font-semibold text-slate-600">Kembalian</span>
                            <span class="font-extrabold text-sm" :class="changeAmount < 0 ? 'text-red-500' : 'text-emerald-600'" x-text="formatRupiah(Math.max(changeAmount, 0))"></span>
                        </div>
                    </div>
                </template>

                <template x-if="errorMessage">
                    <p class="text-xs text-red-500" x-text="errorMessage"></p>
                </template>

                <button @click="submitOrder()"
                        :disabled="!canCheckout || submitting"
                        class="w-full py-3.5 px-4 rounded-xl bg-primary hover:bg-primary-hover active:scale-[0.99] disabled:opacity-40 disabled:cursor-not-allowed text-white font-extrabold text-sm flex items-center justify-center gap-2 shadow-lg shadow-emerald-600/30 transition">
                    <span class="material-symbols-rounded text-lg">print</span>
                    <span x-show="!submitting">Proses Transaksi &amp; Cetak Struk</span>
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

@php
    $categoriesForJs = $categories->map(fn ($category) => [
        'id' => $category->id,
        'name' => $category->name,
        'products' => $category->products->map(fn ($product) => [
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->price,
            'stock' => $product->stock,
            'low_stock_threshold' => $product->low_stock_threshold,
            'image' => $product->image,
            'category_name' => $category->name,
        ]),
    ]);
@endphp

<script>
    function posApp() {
        return {
            categories: @json($categoriesForJs),
            activeCategory: 'all',
            searchQuery: '',
            viewMode: 'grid',
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
                const base = this.activeCategory === 'all'
                    ? this.allProducts
                    : (this.categories.find(c => c.id === this.activeCategory)?.products ?? []);

                const query = this.searchQuery.trim().toLowerCase();
                if (!query) return base;

                return base.filter(p => p.name.toLowerCase().includes(query));
            },

            categoryEmoji(name) {
                const key = (name || '').toLowerCase();
                if (key.includes('non-coffee') || key.includes('non coffee')) return '🧋';
                if (key.includes('coffee') || key.includes('kopi')) return '☕';
                if (key.includes('pastry') || key.includes('roti') || key.includes('kue')) return '🥐';
                if (key.includes('beverage') || key.includes('minuman')) return '💧';
                return '🍽️';
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

            clearCart() {
                this.cart = [];
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