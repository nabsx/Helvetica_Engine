<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminExpenseController extends Controller
{
    public function index(): View
    {
        return view('admin.expenses.index');
    }

    public function store(array $data): Expense
    {
        $data['expense_number'] = Expense::nextNumber($data['expense_date']);
        return Expense::create($data);
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $expense->delete();
        return back()->with('status', 'Expense dihapus.');
    }
}
