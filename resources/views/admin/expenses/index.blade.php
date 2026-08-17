<x-admin-layout title="Expenses">
    <div class="mb-6"><p class="text-sm font-semibold text-emerald-600">Keuangan</p><h1 class="mt-1 text-3xl font-black tracking-tight">Expenses</h1><p class="mt-2 text-sm text-slate-500">Catat biaya operasional untuk menghitung Net Profit harian.</p></div>
    @if (session('status'))<div class="mb-5 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">{{ session('status') }}</div>@endif
    @livewire('expense-manager')
</x-admin-layout>
