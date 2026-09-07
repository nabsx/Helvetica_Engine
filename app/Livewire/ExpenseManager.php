<?php

namespace App\Livewire;

use App\Models\CashMovement;
use App\Models\Expense;
use App\Models\Shift;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class ExpenseManager extends Component
{
    use WithPagination;

    public string $date = '';
    public string $category = 'Operasional';
    public string $description = '';
    public string $amount = '';
    public string $paymentSource = 'cash_drawer';
    public string $search = '';
    public string $period = 'today';
    public string $filterCategory = 'all';
    public ?int $selectedExpense = null;

    protected function rules(): array
    {
        return [
            'date' => ['required', 'date'],
            'category' => ['required', 'string', 'max:80'],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'paymentSource' => ['required', 'in:cash_drawer,petty_cash'],
        ];
    }

    public function mount(): void
    {
        $this->date = now('Asia/Jakarta')->toDateString();
    }

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedPeriod(): void { $this->resetPage(); }
    public function updatedFilterCategory(): void { $this->resetPage(); }
    public function resetForm(): void
    {
        $this->reset('description', 'amount');
        $this->date = now('Asia/Jakarta')->toDateString();
        $this->category = 'Operasional';
        $this->paymentSource = 'cash_drawer';
        $this->resetValidation();
    }

    public function save(): void
    {
        $this->validate();
        $shift = $this->paymentSource === 'cash_drawer'
            ? Shift::query()->where('user_id', auth()->id())->open()->latest('start_time')->first()
            : null;

        if ($this->paymentSource === 'cash_drawer' && ! $shift) {
            $this->addError('paymentSource', 'Buka shift kasir terlebih dahulu untuk memakai Kas Laci Kasir.');
            return;
        }

        DB::transaction(function () use ($shift): void {
            $expense = Expense::create([
                'expense_number' => Expense::nextNumber($this->date),
                'expense_date' => $this->date,
                'category' => $this->category,
                'description' => $this->description,
                'amount' => $this->amount,
                'payment_source' => $this->paymentSource,
                'created_by' => auth()->id(),
            ]);

            if ($shift) {
                CashMovement::create([
                    'shift_id' => $shift->id,
                    'user_id' => auth()->id(),
                    'type' => 'out',
                    'amount' => $this->amount,
                    'category' => 'expense',
                    'description' => $this->description,
                    'reference_type' => Expense::class,
                    'reference_id' => $expense->id,
                ]);
            }
        });

        $this->resetForm();
        $this->dispatch('expense-created');
        session()->flash('status', 'Expense berhasil disimpan.');
    }

    public function show(int $id): void { $this->selectedExpense = $id; }
    public function closeDetail(): void { $this->selectedExpense = null; }
    public function delete(int $id): void
    {
        $expense = Expense::query()->findOrFail($id);
        $expense->delete();
        session()->flash('status', 'Expense dihapus. Mutasi kas tetap tersimpan sebagai audit trail.');
    }

    public function render()
    {
        $base = Expense::query();
        $today = now('Asia/Jakarta')->toDateString();
        $start = match ($this->period) {
            'week' => now('Asia/Jakarta')->startOfWeek()->toDateString(),
            'month' => now('Asia/Jakarta')->startOfMonth()->toDateString(),
            default => $today,
        };
        $filtered = (clone $base)->when($this->period !== 'all', fn ($q) => $q->whereBetween('expense_date', [$start, $today]))
            ->when($this->filterCategory !== 'all', fn ($q) => $q->where('category', $this->filterCategory))
            ->when($this->search, fn ($q) => $q->where(fn ($query) => $query->where('expense_number', 'like', "%{$this->search}%")->orWhere('description', 'like', "%{$this->search}%")));
        $expenses = (clone $filtered)->with('creator')->latest('expense_date')->latest()->paginate(10);
        $monthTotal = (clone $base)->whereBetween('expense_date', [now('Asia/Jakarta')->startOfMonth()->toDateString(), $today])->sum('amount');
        $todayTotal = (clone $base)->whereDate('expense_date', $today)->sum('amount');
        $largest = (clone $base)->latest('amount')->first();
        $selected = $this->selectedExpense ? Expense::with('creator')->find($this->selectedExpense) : null;
        $categories = Expense::query()->select('category')->distinct()->orderBy('category')->pluck('category');
        return view('livewire.expense-manager', compact('expenses', 'todayTotal', 'monthTotal', 'largest', 'selected', 'categories'));
    }
}
