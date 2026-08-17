<?php

namespace App\Livewire;

use App\Models\Expense;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class ExpenseManager extends Component
{
    use WithPagination;

    public string $date = '';
    public string $category = 'Operasional';
    public string $description = '';
    public string $amount = '';
    public string $search = '';

    protected function rules(): array
    {
        return ['date' => ['required', 'date'], 'category' => ['required', 'string', 'max:80'], 'description' => ['required', 'string', 'max:255'], 'amount' => ['required', 'numeric', 'gt:0']];
    }

    public function mount(): void { $this->date = now('Asia/Jakarta')->toDateString(); }
    public function updatedSearch(): void { $this->resetPage(); }

    public function save(): void
    {
        $this->validate();
        Expense::create(['expense_number' => Expense::nextNumber($this->date), 'expense_date' => $this->date, 'category' => $this->category, 'description' => $this->description, 'amount' => $this->amount, 'created_by' => auth()->id()]);
        $this->reset('description', 'amount');
        $this->dispatch('expense-created');
        session()->flash('status', 'Expense tersimpan.');
    }

    public function delete(int $id): void { Expense::query()->findOrFail($id)->delete(); session()->flash('status', 'Expense dihapus.'); }

    public function render()
    {
        $expenses = Expense::query()->with('creator')->when($this->search, fn ($q) => $q->where(fn ($query) => $query->where('expense_number', 'like', "%{$this->search}%")->orWhere('description', 'like', "%{$this->search}%")))->latest('expense_date')->latest()->paginate(10);
        return view('livewire.expense-manager', compact('expenses'));
    }
}
