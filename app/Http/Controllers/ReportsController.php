<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Enrollment;
use App\Models\Expense;
use App\Models\JournalEntry;
use App\Models\Payment;
use App\Models\SchoolClass;
use App\Models\Score;
use App\Models\Student;
use App\Models\StudentCard;
use App\Models\User;
use App\Services\AttendanceCalculationService;
use App\Services\StudentCardStatusService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportsController extends Controller
{
    public function dashboard(Request $request)
    {
        $branchId = $this->branchId($request);
        $students = Student::forBranch($branchId);
        $attendance = Attendance::forBranch($branchId)->get();
        $payments = Payment::forBranch($branchId)->where('status', 'posted');
        $expenses = Expense::forBranch($branchId)->where('status', 'approved');
        return view('reports.dashboard', [
            'students' => (clone $students)->count(), 'activeStudents' => (clone $students)->where('status', 'active')->count(),
            'classes' => SchoolClass::forBranch($branchId)->count(), 'teachers' => User::where('role', 'teacher')->where(fn ($q) => $branchId ? $q->where('branch_id', $branchId) : $q)->count(),
            'attendance' => AttendanceCalculationService::summary($attendance), 'averageScore' => round((float) Score::forBranch($branchId)->avg('score'), 2),
            'paymentTotal' => (clone $payments)->sum('total_amount'), 'income' => (clone $payments)->sum('total_amount'), 'expenses' => (clone $expenses)->sum('amount'),
            'cards' => StudentCard::forBranch($branchId)->where('status', 'active')->count(),
        ]);
    }

    public function show(Request $request, string $type)
    {
        abort_unless(in_array($type, $this->types(), true), 404);
        $dataset = $this->dataset($request, $type);
        [$columns, $rows, $summary] = $dataset;
        $records = $dataset[3] ?? null;
        return view('reports.index', compact('type', 'columns', 'rows', 'summary', 'records'));
    }

    public function export(Request $request, string $type)
    {
        abort_unless(in_array($type, $this->types(), true), 404);
        [$columns, $rows] = $this->dataset($request, $type);
        return response()->streamDownload(function () use ($columns, $rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $columns);
            foreach ($rows as $row) fputcsv($handle, $row);
            fclose($handle);
        }, $type . '-report.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function pdf(Request $request, string $type)
    {
        abort_unless(in_array($type, $this->types(), true), 404);
        [$columns, $rows, $summary] = $this->dataset($request, $type);
        return Pdf::loadView('reports.print', compact('type', 'columns', 'rows', 'summary'))->setPaper('a4', 'landscape')->download($type . '-report.pdf');
    }

    private function dataset(Request $request, string $type): array
    {
        $branchId = $this->branchId($request);
        $from = $request->date('from')?->startOfDay(); $to = $request->date('to')?->endOfDay();
        return match ($type) {
            'students' => $this->students($branchId, $request),
            'enrollments' => $this->enrollments($branchId, $request),
            'classrooms' => $this->classrooms($branchId),
            'attendance' => $this->attendance($branchId, $request, $from, $to),
            'scores' => $this->scores($branchId, $request),
            'payments' => $this->payments($branchId, $request, $from, $to),
            'finance' => $this->finance($branchId, $from, $to),
            'digital-cards' => $this->cards($branchId, $request),
        };
    }

    private function students(?int $branchId, Request $request): array
    {
        $query = Student::forBranch($branchId)->with(['enrollments.schoolClass', 'enrollments.academicYear']);
        if ($request->filled('search')) $query->where(fn ($q) => $q->where('name_kh', 'like', '%' . $request->input('search') . '%')->orWhere('code', 'like', '%' . $request->input('search') . '%'));
        if ($request->filled('status')) $query->where('status', $request->input('status'));
        $rows = $query->latest()->get()->map(fn ($s) => [$s->code, $s->name_kh, $s->gender, $s->dob?->format('Y-m-d'), $s->phone, $s->status, $s->enrollments->where('status', 'active')->first()?->schoolClass?->name])->all();
        return [['Code', 'Student', 'Gender', 'DOB', 'Phone', 'Status', 'Current Class'], $rows, ['Total' => count($rows)]];
    }

    private function enrollments(?int $branchId, Request $request): array
    {
        $query = Enrollment::forBranch($branchId)->with(['student', 'academicYear', 'grade', 'schoolClass']);
        if ($request->filled('status')) $query->where('status', $request->input('status'));
        $rows = $query->latest('enrollment_date')->get()->map(fn ($e) => [$e->student?->code, $e->student?->name_kh, $e->academicYear?->name, $e->grade?->name, $e->schoolClass?->name, $e->enrollment_date?->format('Y-m-d'), $e->status])->all();
        return [['Code', 'Student', 'Academic Year', 'Grade', 'Class', 'Date', 'Status'], $rows, ['Total' => count($rows)]];
    }

    private function classrooms(?int $branchId): array
    {
        $classes = SchoolClass::forBranch($branchId)->with(['grade', 'academicYear', 'teacher'])->withCount(['students as active_students' => fn ($q) => $q->where('status', 'active')])->get();
        $rows = $classes->map(fn ($c) => [$c->name, $c->grade?->name, $c->academicYear?->name, $c->teacher?->display_name, $c->active_students, $c->capacity, max(0, $c->capacity - $c->active_students), $c->capacity ? round(($c->active_students / $c->capacity) * 100, 2) . '%' : '0%'])->all();
        return [['Class', 'Grade', 'Academic Year', 'Teacher', 'Students', 'Capacity', 'Available', 'Capacity %'], $rows, ['Total Classes' => count($rows)]];
    }

    private function attendance(?int $branchId, Request $request, $from, $to): array
    {
        $query = Attendance::forBranch($branchId)->with(['student', 'schoolClass', 'marker']);
        if ($from) $query->whereDate('attendance_date', '>=', $from); if ($to) $query->whereDate('attendance_date', '<=', $to);
        if ($request->filled('status')) $query->where('status', $request->input('status'));
        $records = $query->latest('attendance_date')->get(); $summary = AttendanceCalculationService::summary($records);
        $rows = $records->map(fn ($a) => [$a->attendance_date?->format('Y-m-d'), $a->student?->code, $a->student?->name_kh, $a->schoolClass?->name, $a->status, $a->check_in_time, $a->reason, $a->marker?->display_name])->all();
        return [['Date', 'Code', 'Student', 'Class', 'Status', 'Check In', 'Reason', 'Marked By'], $rows, $summary];
    }

    private function scores(?int $branchId, Request $request): array
    {
        $query = Score::forBranch($branchId)->with(['student', 'subject.parent', 'schoolClass', 'term']);
        foreach (['academic_year_id', 'term_id', 'class_id', 'subject_id'] as $filter) if ($request->filled($filter)) $query->where($filter, $request->input($filter));
        $scores = $query->latest()->get(); $average = round((float) $scores->avg('score'), 2);
        $rows = $scores->map(fn ($s) => [$s->student?->code, $s->student?->name_kh, $s->subject?->parent?->name_kh ? $s->subject->parent->name_kh . ' > ' . $s->subject->name_kh : $s->subject?->name_kh, $s->schoolClass?->name, $s->term?->name, $s->score, $s->maximum_score, $s->grade, $s->result])->all();
        return [['Code', 'Student', 'Subject', 'Class', 'Term', 'Score', 'Maximum', 'Grade', 'Result'], $rows, ['Total' => count($rows), 'Average' => $average]];
    }

    private function payments(?int $branchId, Request $request, $from, $to): array
    {
        $view = in_array($request->input('view'), ['transactions', 'income', 'expired', 'owing'], true) ? $request->input('view') : 'transactions';
        $records = null;
        if ($view === 'transactions') {
            [$columns, $rows, $records] = $this->paymentsTransactionRows($branchId, $request, $from, $to);
        } else {
            [$columns, $rows] = match ($view) {
                'income' => $this->paymentsIncomeRows($branchId, $from, $to),
                'expired' => $this->paymentsExpiredRows($branchId),
                'owing' => $this->paymentsOwingRows($branchId),
            };
        }
        return [$columns, $rows, $this->paymentsSummary($branchId, $from, $to), $records];
    }

    private function paymentsSummary(?int $branchId, $from, $to): array
    {
        $query = Payment::forBranch($branchId)->where('status', 'posted');
        if ($from) $query->whereDate('payment_date', '>=', $from); if ($to) $query->whereDate('payment_date', '<=', $to);
        $payments = $query->get();
        $overdueEnrollments = $this->overdueActiveEnrollments($branchId);
        return [
            'ប្រតិបត្តិការណ៍' => $payments->count(),
            'ចំណូល' => $payments->sum('total_amount'),
            'សិស្សផុតកំណត់' => $overdueEnrollments->count(),
            'សិស្សជំពាក់' => $overdueEnrollments->pluck('student_id')->unique()->count(),
        ];
    }

    private function overdueActiveEnrollments(?int $branchId)
    {
        $today = Carbon::today();
        return Enrollment::forBranch($branchId)->where('status', 'active')->whereNotNull('academic_year_id')
            ->with(['student', 'grade', 'schoolClass', 'payments' => fn ($q) => $q->where('status', 'posted')->latest('payment_date')->latest('id')->limit(1)])
            ->get()
            ->filter(fn ($enrollment) => $enrollment->payments->first()?->next_payment_date?->lt($today));
    }

    private function paymentsTransactionRows(?int $branchId, Request $request, $from, $to): array
    {
        $query = Payment::forBranch($branchId)->where('status', 'posted')
            ->with(['student', 'creator', 'invoice', 'enrollment.schoolClass.shift']);
        if ($from) $query->whereDate('payment_date', '>=', $from); if ($to) $query->whereDate('payment_date', '<=', $to); if ($request->filled('currency')) $query->where('currency', $request->input('currency')); if ($request->filled('payment_method')) $query->where('payment_method', $request->input('payment_method'));
        if ($search = $request->input('search')) {
            $query->where(fn ($q) => $q->where('reference', 'like', "%{$search}%")
                ->orWhereHas('invoice', fn ($invoiceQuery) => $invoiceQuery->where('invoice_number', 'like', "%{$search}%"))
                ->orWhereHas('student', fn ($studentQuery) => $studentQuery->where('name_kh', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%")->orWhere('student_code', 'like', "%{$search}%")));
        }
        $payments = $query->orderByDesc('payment_date')->orderByDesc('id')->get();
        $rows = $payments->values()->map(fn ($p, $index) => [
            $index + 1,
            $p->payment_date?->format('d/m/Y'),
            $p->invoice?->invoice_number ?? $p->reference,
            $p->student?->name_kh,
            $p->enrollment?->schoolClass?->name,
            $p->enrollment?->schoolClass?->shift?->name,
            $this->formatCurrency($p->currency, (float) $p->total_amount),
            $this->paymentMethodLabel($p),
            $p->creator?->display_name,
            $p->notes ?: '-',
        ])->all();
        return [['#', 'ថ្ងៃបង់ប្រាក់', 'លេខវិក្កយបត្រ', 'ឈ្មោះសិស្ស', 'ថ្នាក់', 'វេនសិក្សា', 'ចំនួនទឹកប្រាក់', 'វិធីសាស្រ្ត', 'អ្នកប្រើប្រាស់', 'បរិយាយ'], $rows, $payments];
    }

    private function formatCurrency(string $currency, float $amount): string
    {
        return $currency === 'KHR' ? '៛' . number_format($amount, 0) : '$' . number_format($amount, 2);
    }

    private function paymentMethodLabel(Payment $payment): string
    {
        return match ($payment->payment_method) {
            'cash' => 'សាច់ប្រាក់',
            'bank' => 'ធនាគារ (ABA/ACLEDA)',
            'qr' => 'QR (WING/ACLEDA)',
            'other' => $payment->other_bank_name ?: 'ផ្សេងៗ',
            default => 'ផ្សេងៗ',
        };
    }

    private function paymentsIncomeRows(?int $branchId, $from, $to): array
    {
        $query = Payment::forBranch($branchId)->where('status', 'posted');
        if ($from) $query->whereDate('payment_date', '>=', $from); if ($to) $query->whereDate('payment_date', '<=', $to);
        $rows = $query->get()
            ->groupBy(fn ($p) => $p->payment_date?->format('Y-m'))
            ->sortKeys()
            ->map(fn ($group, $month) => [$month, $group->count(), round($group->sum('total_amount'), 2)])
            ->values()->all();
        return [['Month', 'Transactions', 'Income'], $rows];
    }

    private function paymentsExpiredRows(?int $branchId): array
    {
        $today = Carbon::today();
        $rows = Enrollment::forBranch($branchId)->where('status', 'active')->whereNotNull('academic_year_id')
            ->with(['student', 'schoolClass', 'payments' => fn ($q) => $q->where('status', 'posted')->latest('payment_date')->latest('id')->limit(1)])
            ->get()
            ->filter(fn ($enrollment) => $enrollment->payments->first()?->paid_until?->lt($today))
            ->map(fn ($enrollment) => [
                $enrollment->student?->student_code,
                $enrollment->student?->name_kh,
                $enrollment->schoolClass?->name,
                $enrollment->payments->first()?->paid_until?->format('Y-m-d'),
                $enrollment->payments->first()?->paid_until?->diffInDays($today),
            ])->values()->all();
        return [['Code', 'Student', 'Class', 'Paid Until', 'Days Expired'], $rows];
    }

    private function paymentsOwingRows(?int $branchId): array
    {
        $today = Carbon::today();
        $rows = $this->overdueActiveEnrollments($branchId)
            ->map(function ($enrollment) use ($today) {
                $lastPayment = $enrollment->payments->first();
                $monthlyFee = (float) ($enrollment->grade?->monthly_tuition_fee ?? 0);
                $overdueMonths = max(1, $lastPayment?->next_payment_date?->diffInMonths($today) + 1);
                return [
                    $enrollment->student?->student_code,
                    $enrollment->student?->name_kh,
                    $enrollment->schoolClass?->name,
                    $lastPayment?->next_payment_date?->format('Y-m-d'),
                    round($monthlyFee * $overdueMonths, 2),
                ];
            })->values()->all();
        return [['Code', 'Student', 'Class', 'Due Since', 'Estimated Amount Owed'], $rows];
    }

    private function finance(?int $branchId, $from, $to): array
    {
        $payments = Payment::forBranch($branchId)->where('status', 'posted')->when($from, fn ($q) => $q->whereDate('payment_date', '>=', $from))->when($to, fn ($q) => $q->whereDate('payment_date', '<=', $to))->get();
        $expenses = Expense::forBranch($branchId)->where('status', 'approved')->when($from, fn ($q) => $q->whereDate('expense_date', '>=', $from))->when($to, fn ($q) => $q->whereDate('expense_date', '<=', $to))->get();
        $rows = $payments->map(fn ($p) => [$p->payment_date?->format('Y-m-d'), $p->reference, 'Income', $p->currency, $p->total_amount])->concat($expenses->map(fn ($e) => [$e->expense_date?->format('Y-m-d'), $e->reference, 'Expense', $e->currency, $e->amount]))->all();
        return [['Date', 'Reference', 'Type', 'Currency', 'Amount'], $rows, ['Income' => $payments->sum('total_amount'), 'Expenses' => $expenses->sum('amount')]];
    }

    private function cards(?int $branchId, Request $request): array
    {
        $cards = StudentCard::forBranch($branchId)->with(['student', 'academicYear'])->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))->get();
        foreach ($cards as $card) StudentCardStatusService::sync($card);
        $rows = $cards->map(fn ($c) => [$c->student?->code, $c->student?->name_kh, $c->card_number, $c->academicYear?->name, $c->issue_date?->format('Y-m-d'), $c->expiry_date?->format('Y-m-d'), StudentCardStatusService::current($c)])->all();
        return [['Code', 'Student', 'Card Number', 'Academic Year', 'Issued', 'Expires', 'Status'], $rows, ['Total' => count($rows)]];
    }

    private function branchId(Request $request): ?int { return $request->user()->isSuperAdmin() ? null : $request->user()->branch_id; }
    private function types(): array { return ['students', 'enrollments', 'classrooms', 'attendance', 'scores', 'payments', 'finance', 'digital-cards']; }
}
