<!DOCTYPE html>
<html class="dark" lang="id">
<head>
<meta charset="UTF-8">
<meta content="width=device-width, initial-scale=1.0" name="viewport">
<title>Helvetica POS — Kunci Layar Kasir</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
<script defer src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
<link href="https://fonts.googleapis.com" rel="preconnect">
<link crossorigin href="https://fonts.gstatic.com" rel="preconnect">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">
<script>
    tailwind.config = {
      darkMode: "class",
      theme: {
        extend: {
          colors: {
            primary: "#10b981",
            "background-light": "#f1f5f9",
            "background-dark": "#070b14",
          },
          fontFamily: {
            display: ["'Plus Jakarta Sans'", "sans-serif"],
            sans: ["'Inter'", "sans-serif"],
          },
          borderRadius: {
            DEFAULT: "0.75rem",
          },
          boxShadow: {
            'glow-emerald': '0 0 25px -5px rgba(16, 185, 129, 0.35)',
            'key-dark': '0 4px 0 0 #0f172a, inset 0 1px 0 0 rgba(255, 255, 255, 0.08)',
            'key-pressed': '0 1px 0 0 #0f172a, inset 0 2px 4px 0 rgba(0, 0, 0, 0.4)',
          }
        },
      },
    };
</script>
<style>
    .material-symbols-outlined {
      font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24;
    }
    .scrollbar-none::-webkit-scrollbar { display: none; }
    .scrollbar-none { -ms-overflow-style: none; scrollbar-width: none; }
</style>
</head>
<body class="bg-background-light dark:bg-background-dark min-h-screen text-slate-800 dark:text-slate-100 flex flex-col justify-between font-sans selection:bg-emerald-500 selection:text-white relative overflow-x-hidden antialiased">

<div class="fixed inset-0 pointer-events-none overflow-hidden z-0">
    <div class="absolute -top-[20%] left-1/2 -translate-x-1/2 w-[700px] h-[500px] bg-emerald-500/10 dark:bg-emerald-500/15 blur-[120px] rounded-full"></div>
    <div class="absolute bottom-0 left-10 w-[400px] h-[350px] bg-teal-500/5 dark:bg-emerald-600/10 blur-[100px] rounded-full"></div>
    <div class="absolute -bottom-20 right-10 w-[500px] h-[400px] bg-cyan-600/5 dark:bg-cyan-500/10 blur-[130px] rounded-full"></div>
    <div class="absolute inset-0 bg-[radial-gradient(rgba(255,255,255,0.03)_1px,transparent_1px)] [background-size:24px_24px] pointer-events-none opacity-60 dark:opacity-40"></div>
</div>

<main class="relative z-10 flex-1 flex items-center justify-center p-4 sm:p-6 md:p-8"
      x-data="loginPad(@js($staff->map(fn ($m) => [
          'id' => $m->id,
          'name' => $m->name,
          'role' => ucfirst($m->role),
      ])))">
<div class="w-full max-w-md">
<div class="backdrop-blur-xl bg-white/80 dark:bg-slate-900/80 border border-slate-200/90 dark:border-slate-800/90 rounded-3xl shadow-2xl dark:shadow-[0_20px_60px_-15px_rgba(0,0,0,0.7)] p-6 sm:p-8 transition-all">

    <div class="text-center mb-6">
        <div class="inline-flex p-2.5 rounded-2xl bg-emerald-500/10 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 mb-3 shadow-inner">
            <span class="material-symbols-outlined text-2xl">lock_person</span>
        </div>
        <h2 class="text-2xl font-bold font-display tracking-tight text-slate-900 dark:text-white">Otentikasi Kasir</h2>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Pilih profil staff dan masukkan PIN kasir Anda</p>
    </div>

    @if ($errors->any())
        <div class="bg-rose-500/10 border border-rose-500/30 text-rose-400 text-sm rounded-2xl px-4 py-2.5 mb-5">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('pos.login.submit') }}">
        @csrf
        <input type="hidden" name="user_id" :value="selectedUserId">
        <input type="hidden" name="pin" :value="pin">

        <div class="mb-6">
            <div class="flex items-center justify-between mb-2">
                <label class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-xs">badge</span>
                    Petugas Kasir
                </label>
            </div>

            <div class="relative group">
                <button type="button" @click="pickerOpen = !pickerOpen"
                        class="w-full flex items-center justify-between p-3 rounded-2xl bg-slate-100/90 dark:bg-slate-800/80 hover:bg-slate-200/80 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-700/80 transition shadow-sm text-left">
                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-cyan-600 flex items-center justify-center font-bold text-white shadow-md text-sm">
                                <span x-text="initials"></span>
                            </div>
                            <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-emerald-500 border-2 border-white dark:border-slate-900 rounded-full"></span>
                        </div>
                        <div>
                            <div class="font-bold text-sm text-slate-800 dark:text-slate-100 flex items-center gap-2">
                                <span x-text="selectedStaff ? selectedStaff.name : 'Pilih Staff'"></span>
                                <span class="px-1.5 py-0.2 bg-slate-200 dark:bg-slate-700 text-[10px] font-semibold rounded text-slate-600 dark:text-slate-300"
                                      x-show="selectedStaff" x-text="selectedStaff ? 'ID: USR-' + String(selectedStaff.id).padStart(3, '0') : ''"></span>
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400" x-text="selectedStaff ? selectedStaff.role : 'Belum dipilih'"></p>
                        </div>
                    </div>
                    <div class="flex items-center gap-1 text-slate-400 dark:text-slate-400 pl-2">
                        <span class="text-xs font-medium hidden sm:inline text-slate-500">Ganti</span>
                        <span class="material-symbols-outlined text-base transition-transform" :class="pickerOpen ? 'rotate-180' : ''">unfold_more</span>
                    </div>
                </button>
            </div>

            <div x-show="pickerOpen" x-collapse
                 class="mt-3 rounded-2xl bg-slate-100/80 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 p-1.5 space-y-1">
                <div class="flex items-center justify-between px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                    <span>Ganti Profil Cepat</span>
                    <span class="text-emerald-500 font-medium lowercase" x-text="staff.length + ' kasir terdaftar'"></span>
                </div>
                <div class="grid grid-cols-2 gap-1.5">
                    <template x-for="member in staff" :key="member.id">
                        <button type="button" @click="selectUser(member.id); pickerOpen = false"
                                :class="member.id === selectedUserId
                                    ? 'bg-emerald-500/10 dark:bg-emerald-500/15 border-emerald-500/40'
                                    : 'bg-white/60 dark:bg-slate-900/60 hover:bg-slate-200/80 dark:hover:bg-slate-800/80 border-slate-200 dark:border-slate-800'"
                                class="flex items-center gap-2 p-2 rounded-xl border text-left transition-all">
                            <div class="w-7 h-7 rounded-lg flex items-center justify-center font-bold text-xs shrink-0 shadow-sm"
                                 :class="member.id === selectedUserId
                                    ? 'bg-emerald-600 text-white'
                                    : (member.role === 'Admin' ? 'bg-cyan-600/20 text-cyan-400 border border-cyan-500/30' : 'bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300')"
                                 x-text="memberInitials(member.name)"></div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-1">
                                    <span class="text-xs truncate"
                                          :class="member.id === selectedUserId ? 'font-bold text-slate-800 dark:text-slate-100' : 'font-medium text-slate-700 dark:text-slate-300'"
                                          x-text="shortName(member.name)"></span>
                                    <span x-show="member.id === selectedUserId" class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                                    <span x-show="member.role === 'Admin'" class="px-1 py-0.2 bg-cyan-500/10 text-cyan-400 text-[8px] font-semibold rounded">ADMIN</span>
                                </div>
                                <p class="text-[10px] truncate"
                                   :class="member.id === selectedUserId ? 'text-emerald-600 dark:text-emerald-400 font-medium' : 'text-slate-400'"
                                   x-text="member.role"></p>
                            </div>
                        </button>
                    </template>
                </div>
            </div>
        </div>

        <div class="mb-6">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">PIN Kasir</span>
            </div>
            <div class="flex justify-between items-center gap-2 sm:gap-3 bg-slate-100/80 dark:bg-slate-950/60 p-3.5 sm:p-4 rounded-2xl border border-slate-200 dark:border-slate-800">
                <template x-for="i in 6" :key="i">
                    <div class="h-12 flex-1 rounded-xl shadow-sm flex items-center justify-center transition-all"
                         :class="{
                            'bg-white dark:bg-slate-800/90 border-2 border-emerald-500/80': pin.length >= i,
                            'bg-slate-50 dark:bg-slate-900/90 border-2 border-dashed border-emerald-500/70 animate-pulse': pin.length === i - 1,
                            'bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800': pin.length < i - 1
                         }">
                        <span class="rounded-full"
                              :class="pin.length >= i
                                ? 'w-3.5 h-3.5 bg-emerald-500 shadow-md shadow-emerald-500/50'
                                : (pin.length === i - 1 ? 'w-2 h-2 bg-emerald-500/40' : 'w-2 h-2 bg-slate-300 dark:bg-slate-700')"></span>
                    </div>
                </template>
            </div>
        </div>

        <div class="grid grid-cols-3 gap-2.5 sm:gap-3 mb-6 select-none">
            <template x-for="digit in [1,2,3,4,5,6,7,8,9]" :key="digit">
                <button type="button" @click="press(digit)"
                        class="h-14 rounded-2xl bg-white dark:bg-slate-800/90 hover:bg-slate-50 dark:hover:bg-slate-700/80 active:translate-y-0.5 border border-slate-200/80 dark:border-slate-700/70 shadow-sm flex flex-col items-center justify-center transition text-slate-800 dark:text-slate-100">
                    <span class="text-xl font-bold font-display leading-tight" x-text="digit"></span>
                </button>
            </template>

            <button type="button" @click="clear()"
                    class="h-14 rounded-2xl bg-rose-500/10 hover:bg-rose-500/20 active:translate-y-0.5 border border-rose-500/20 text-rose-600 dark:text-rose-400 shadow-sm flex flex-col items-center justify-center transition">
                <span class="text-xs font-bold uppercase tracking-wider">Hapus</span>
            </button>

            <button type="button" @click="press(0)"
                    class="h-14 rounded-2xl bg-white dark:bg-slate-800/90 hover:bg-slate-50 dark:hover:bg-slate-700/80 active:translate-y-0.5 border border-slate-200/80 dark:border-slate-700/70 shadow-sm flex flex-col items-center justify-center transition text-slate-800 dark:text-slate-100">
                <span class="text-xl font-bold font-display leading-tight">0</span>
            </button>

            <button type="button" @click="backspace()"
                    class="h-14 rounded-2xl bg-slate-100/90 dark:bg-slate-800/90 hover:bg-slate-200/80 dark:hover:bg-slate-700/80 active:translate-y-0.5 border border-slate-200/80 dark:border-slate-700/70 shadow-sm flex items-center justify-center transition text-slate-600 dark:text-slate-300">
                <span class="material-symbols-outlined text-2xl">backspace</span>
            </button>
        </div>

        <button type="submit" :disabled="!selectedUserId || pin.length < 4"
                class="w-full h-14 bg-gradient-to-r from-emerald-600 to-teal-500 hover:from-emerald-500 hover:to-teal-400 active:scale-[0.99] disabled:opacity-40 disabled:cursor-not-allowed text-white font-bold font-display rounded-2xl shadow-lg shadow-emerald-500/25 flex items-center justify-center gap-3 text-base tracking-wide transition group">
            <span class="material-symbols-outlined text-2xl transition-transform group-hover:translate-x-0.5">login</span>
            <span>Buka &amp; Masuk Terminal POS</span>
        </button>
    </form>

</div>
</div>
</main>

<script>
    function loginPad(staff) {
        return {
            staff: staff,
            selectedUserId: staff.length ? staff[0].id : null,
            pickerOpen: false,
            pin: '',
            get selectedStaff() {
                return this.staff.find(s => s.id === this.selectedUserId) || null;
            },
            get initials() {
                return this.selectedStaff ? this.memberInitials(this.selectedStaff.name) : '?';
            },
            memberInitials(name) {
                return name
                    .split(' ')
                    .map(w => w[0])
                    .join('')
                    .slice(0, 2)
                    .toUpperCase();
            },
            shortName(name) {
                const parts = name.trim().split(' ');
                if (parts.length === 1) return parts[0];
                return parts[0] + ' ' + parts[1][0].toUpperCase() + '.';
            },
            selectUser(id) {
                this.selectedUserId = id;
            },
            press(digit) {
                if (this.pin.length < 6) this.pin += digit;
            },
            backspace() {
                this.pin = this.pin.slice(0, -1);
            },
            clear() {
                this.pin = '';
            },
        };
    }
</script>
</body>
</html>