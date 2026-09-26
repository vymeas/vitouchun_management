<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class FinanceReportController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->date('from')?->startOfDay() ?? Carbon::now()->startOfMonth();
        $to = $request->date('to')?->endOfDay() ?? Carbon::now()->endOfDay();
        abort_if($from->gt($to), 422, 'កាលបរិច្ឆេទមិនត្រឹមត្រូវ។');
        $branchId = $request->user()->isSuperAdmin() ? null : $request->user()->branch_id;
        $payments = Payment::with(['student', 'refunds'])->forBranch($branchId)->where('status', 'posted')->whereBetween('payment_date', [$from, $to])->latest('payment_date')->get();
        $expenses = Expense::forBranch($branchId)->where('status', 'approved')->whereBetween('expense_date', [$from, $to])->latest('expense_date')->get();
        $income = (float) $payments->sum(fn ($payment) => (float) ($payment->received_amount ?? $payment->total_amount) - (float) $payment->refunds->where('status', 'completed')->sum('amount'));
        $refunded = (float) $payments->sum(fn ($payment) => (float) $payment->refunds->where('status', 'completed')->sum('amount'));
        $expense = (float) $expenses->sum('amount');
        return view('finance.reports', compact('from', 'to', 'payments', 'expenses', 'income', 'expense', 'refunded'));
    }

    public function paymentsCsv(Request $request)
    {
        $from = $request->date('from')?->startOfDay() ?? Carbon::now()->startOfMonth();
        $to = $request->date('to')?->endOfDay() ?? Carbon::now()->endOfDay();
        $branchId = $request->user()->isSuperAdmin() ? null : $request->user()->branch_id;
        $payments = Payment::with('student')->forBranch($branchId)->where('status', 'posted')->whereBetween('payment_date', [$from, $to])->cursor();
        return response()->streamDownload(function () use ($payments) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Reference', 'Date', 'Student', 'Currency', 'Amount', 'Method']);
            foreach ($payments as $payment) fputcsv($handle, [$payment->reference, $payment->payment_date->format('Y-m-d'), $payment->student?->name_kh, $payment->currency, $payment->total_amount, $payment->payment_method]);
            fclose($handle);
        }, 'payment-report.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
