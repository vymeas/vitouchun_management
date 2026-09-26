<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class FinanceDashboardController extends Controller
{
    public function index(Request $request)
    {
        [$from, $to] = $this->range($request);
        $branchId = $request->user()->isSuperAdmin() ? null : $request->user()->branch_id;
        $payments = Payment::with('refunds')->forBranch($branchId)->whereBetween('payment_date', [$from, $to])->where('status', 'posted');
        $expenses = Expense::forBranch($branchId)->whereBetween('expense_date', [$from, $to])->where('status', 'approved');
        $paymentRows = (clone $payments)->get();
        $income = (float) $paymentRows->sum(fn ($payment) => (float) ($payment->received_amount ?? $payment->total_amount) - (float) $payment->refunds->where('status', 'completed')->sum('amount'));
        $refunded = (float) $paymentRows->sum(fn ($payment) => (float) $payment->refunds->where('status', 'completed')->sum('amount'));
        $expense = (float) $expenses->sum('amount');
        $paidStudents = (clone $payments)->distinct('student_id')->count('student_id');

        return view('finance.dashboard', compact('from', 'to', 'income', 'expense', 'refunded', 'paidStudents'));
    }

    private function range(Request $request): array
    {
        $from = $request->date('from')?->startOfDay() ?? Carbon::now()->startOfMonth();
        $to = $request->date('to')?->endOfDay() ?? Carbon::now()->endOfDay();
        abort_if($from->gt($to), 422, 'កាលបរិច្ឆេទចាប់ផ្តើមមិនអាចលើសកាលបរិច្ឆេទបញ្ចប់ទេ។');
        return [$from, $to];
    }
}
