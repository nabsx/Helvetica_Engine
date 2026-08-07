<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Helvetica POS — Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center">

    <div x-data="loginPad()" class="bg-slate-800 rounded-2xl shadow-xl p-8 w-full max-w-sm text-white">
        <h1 class="text-2xl font-bold text-center mb-1">Helvetica POS</h1>
        <p class="text-slate-400 text-center mb-6">Pilih nama & masukkan PIN</p>

        @if ($errors->any())
            <div class="bg-red-500/20 border border-red-500 text-red-200 text-sm rounded-lg px-4 py-2 mb-4">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('pos.login.submit') }}">
            @csrf
            <input type="hidden" name="pin" x-model="pin">

            <label class="block text-sm text-slate-400 mb-1">Nama Staff</label>
            <select name="user_id" x-model="selectedUser" required
                    class="w-full mb-4 rounded-lg bg-slate-700 border-slate-600 text-white px-3 py-2">
                <option value="" disabled selected>-- Pilih Nama --</option>
                @foreach ($staff as $member)
                    <option value="{{ $member->id }}">{{ $member->name }} ({{ ucfirst($member->role) }})</option>
                @endforeach
            </select>

            <label class="block text-sm text-slate-400 mb-1">PIN</label>
            <div class="flex justify-center gap-2 mb-6">
                <template x-for="i in 6" :key="i">
                    <div class="w-8 h-10 rounded-lg bg-slate-700 flex items-center justify-center text-xl font-bold">
                        <span x-text="pin.length >= i ? '●' : ''"></span>
                    </div>
                </template>
            </div>

            <div class="grid grid-cols-3 gap-3 mb-6">
                <template x-for="digit in [1,2,3,4,5,6,7,8,9]" :key="digit">
                    <button type="button" @click="press(digit)"
                            class="bg-slate-700 hover:bg-slate-600 rounded-xl py-4 text-xl font-semibold">
                        <span x-text="digit"></span>
                    </button>
                </template>
                <button type="button" @click="clear()" class="bg-slate-700 hover:bg-slate-600 rounded-xl py-4 text-sm font-semibold">Hapus</button>
                <button type="button" @click="press(0)" class="bg-slate-700 hover:bg-slate-600 rounded-xl py-4 text-xl font-semibold">0</button>
                <button type="button" @click="backspace()" class="bg-slate-700 hover:bg-slate-600 rounded-xl py-4 text-sm font-semibold">⌫</button>
            </div>

            <button type="submit" :disabled="!selectedUser || pin.length < 4"
                    class="w-full bg-emerald-600 hover:bg-emerald-500 disabled:opacity-40 disabled:cursor-not-allowed rounded-xl py-3 font-bold">
                Masuk
            </button>
        </form>
    </div>

    <script>
        function loginPad() {
            return {
                pin: '',
                selectedUser: '',
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
