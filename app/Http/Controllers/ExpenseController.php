<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExpenseRequest;
use App\Models\Branch;
use App\Models\Expense;
use App\Services\FinanceService;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::with('creator');
        if (!$request->user()->isSuperAdmin()) $query->where('branch_id', $request->user()->branch_id);
        if ($search = $request->input('search')) $query->where(fn ($q) => $q->where('reference', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%"));
        $expenses = $query->latest('expense_date')->paginate(15)->withQueryString();
        return view('expenses.index', compact('expenses'));
    }

    public function create(Request $request)
    {
        $branches = $request->user()->isSuperAdmin() ? Branch::active()->orderBy('name')->get() : collect();
        return view('expenses.create', compact('branches'));
    }

    public function store(StoreExpenseRequest $request)
    {
        $data = $request->validated();
        $data['branch_id'] = $request->user()->isSuperAdmin() ? ($data['branch_id'] ?? null) : $request->user()->branch_id;
        abort_unless($data['branch_id'], 422, 'សូមជ្រើសរើសសាខា។');
        $expense = FinanceService::recordExpense($data, $request->user()->id);
        return redirect()->route('expenses.show', $expense)->with('success', 'បានរក្សាទុកចំណាយ និងគណនេយ្យដោយជោគជ័យ។');
    }

    public function show(Expense $expense)
    {
        abort_unless(auth()->user()->isSuperAdmin() || auth()->user()->branch_id === $expense->branch_id, 403);
        $expense->load(['branch', 'creator', 'journalEntry.lines.account']);
        return view('expenses.show', compact('expense'));
    }
}
