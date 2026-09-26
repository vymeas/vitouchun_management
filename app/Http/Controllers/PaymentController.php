<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentRequest;
use App\Models\AcademicYear;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\PaymentMonth;
use App\Models\Service;
use App\Models\StudentCredit;
use App\Models\Refund;
use App\Models\Student;
use App\Services\FinanceService;
use App\Services\PaymentEligibilityService;
use App\Services\PaymentSettlementService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Carbon\CarbonPeriod;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $branchId = $request->user()->isSuperAdmin() ? null : $request->user()->branch_id;
        $today = Carbon::today();

        // One row per student (their latest posted payment) instead of every historical payment.
        $query = Student::forBranch($branchId)
            ->whereHas('payments', fn ($q) => $q->where('status', 'posted'))
            ->with(['latestPayment.invoice', 'latestPayment.creator', 'latestPayment.paymentMonths', 'latestPayment.enrollment.schoolClass.shift']);

        if ($search = $request->input('search')) {
            $query->where(fn ($q) => $q->where('name_kh', 'like', "%{$search}%")
                ->orWhere('khmer_name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")
                ->orWhere('student_code', 'like', "%{$search}%")
                ->orWhereHas('payments', fn ($p) => $p->where('reference', 'like', "%{$search}%")));
        }
        if ($academicYearId = $request->input('academic_year_id')) {
            $query->whereHas('payments', fn ($q) => $q->where('status', 'posted')->where('academic_year_id', $academicYearId));
        }

        $status = $request->input('payment_status');
        $from = $request->date('from');
        $to = $request->date('to');
        $students = $query->get()->filter(function ($student) use ($from, $to, $status, $today) {
            $payment = $student->latestPayment;
            if (!$payment) return false;
            if ($from && $payment->payment_date?->lt($from)) return false;
            if ($to && $payment->payment_date?->gt($to)) return false;
            $isOwing = $this->isPaymentOwing($payment, $today);
            $payment->setAttribute('is_owing', $isOwing);
            if ($status === 'paid') return !$isOwing;
            if ($status === 'owing') return $isOwing;
            return true;
        })->values();

        $perPage = 15;
        $page = (int) $request->input('page', 1);
        $payments = new LengthAwarePaginator(
            $students->forPage($page, $perPage)->values(),
            $students->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $academicYears = AcademicYear::forBranch($branchId)->orderByDesc('start_date')->get();

        return view('payments.index', compact('payments', 'academicYears'));
    }

    // A student owes when their latest payment under-collected (partial settlement) or a later month has since become due.
    private function isPaymentOwing(Payment $payment, Carbon $today): bool
    {
        if (PaymentSettlementService::summary($payment)['status'] === 'partial') return true;
        return (bool) $payment->next_payment_date?->lt($today);
    }

    public function history(Student $student)
    {
        $this->authorizeBranch($student->branch_id);
        $student->load(['branch', 'enrollments' => fn ($q) => $q->where('status', 'active')->with(['schoolClass.shift'])]);
        $activeEnrollment = $student->enrollments->first();
        $today = Carbon::today();
        $paymentHistory = $student->payments()->where('status', 'posted')->with(['invoice', 'creator'])->orderBy('payment_date')->orderBy('id')->get();
        $paymentHistory->each(fn ($payment) => $payment->setAttribute('is_owing', $this->isPaymentOwing($payment, $today)));
        return view('payments.history', compact('student', 'activeEnrollment', 'paymentHistory'));
    }

    public function create(Request $request)
    {
        $branchId = $request->user()->isSuperAdmin() ? null : $request->user()->branch_id;
        $students = Student::forBranch($branchId)->whereHas('enrollments', fn ($enrollmentQuery) => $enrollmentQuery->where('status', 'active')->whereNotNull('academic_year_id')->whereNotNull('grade_id')->whereNotNull('class_id')->whereColumn('enrollments.branch_id', 'students.branch_id'))->with(['enrollments' => fn ($enrollmentQuery) => $enrollmentQuery->where('status', 'active')->whereNotNull('academic_year_id')->whereNotNull('grade_id')->whereNotNull('class_id')->with(['academicYear', 'grade', 'schoolClass.teacher'])->latest('enrollment_date')])->where('status', 'active')
            ->when($request->filled('search'), fn ($query) => $query->where(function ($studentQuery) use ($request) {
                $search = $request->input('search');
                $studentQuery->where('code', 'like', "%{$search}%")
                    ->orWhere('student_code', 'like', "%{$search}%")
                    ->orWhere('name_kh', 'like', "%{$search}%")
                    ->orWhere('khmer_name', 'like', "%{$search}%")
                    ->orWhere('name_en', 'like', "%{$search}%")
                    ->orWhere('english_name', 'like', "%{$search}%");
            }))->orderBy('name_kh')->paginate(10)->withQueryString();
        $academicYears = AcademicYear::forBranch($branchId)->active()->orderByDesc('start_date')->get();
        $selectedStudentId = (int) ($request->query('student_id') ?? $request->input('student_id') ?? 0);
        $selectedInvoiceId = (int) ($request->query('invoice_id') ?? $request->input('invoice_id') ?? 0);
        $selectedEnrollmentId = (int) ($request->query('enrollment_id') ?? $request->input('enrollment_id') ?? 0);
        $enrollments = $selectedStudentId > 0
            ? Enrollment::with(['student', 'academicYear', 'grade', 'schoolClass.teacher'])->where('student_id', $selectedStudentId)->where('status', 'active')->whereNotNull('academic_year_id')->whereNotNull('grade_id')->whereNotNull('class_id')->orderByDesc('enrollment_date')->get()
            : collect();
        $selectedEnrollment = $enrollments->firstWhere('id', $selectedEnrollmentId) ?: $enrollments->first();
        $selectedEnrollmentId = $selectedEnrollment?->id ?? $selectedEnrollmentId;
        $selectedAcademicYear = $selectedEnrollment?->academicYear ?: $academicYears->firstWhere('id', (int) $request->query('academic_year_id'));
        $monthlyTuitionFee = (float) ($selectedEnrollment?->grade?->monthly_tuition_fee ?? 0);
        $serviceBranchId = $selectedEnrollment?->branch_id ?: $branchId;
        $services = Service::forBranch($serviceBranchId)->active()->orderBy('name_kh')->get();
        $monthlyPeriods = $selectedAcademicYear ? collect(CarbonPeriod::create(Carbon::parse($selectedAcademicYear->start_date)->startOfMonth(), '1 month', Carbon::parse($selectedAcademicYear->end_date)->startOfMonth())) : collect();
        $paidMonthKeys = $selectedEnrollment ? PaymentMonth::where('enrollment_id', $selectedEnrollment->id)->where('academic_year_id', $selectedAcademicYear?->id)->where('type', 'tuition')->pluck('month_key')->all() : [];
        $paidAdminMonthKeys = $selectedEnrollment ? PaymentMonth::where('enrollment_id', $selectedEnrollment->id)->where('academic_year_id', $selectedAcademicYear?->id)->where('type', 'administrative')->pluck('month_key')->all() : [];
        $paymentHistory = $selectedEnrollment?->payments()->orderBy('payment_date')->get(['payment_date', 'paid_until', 'next_payment_date']) ?? collect();
        $originalStartDate = $selectedEnrollment?->enrollment_date ?: $paymentHistory->first()?->payment_date;
        $lastPayment = $paymentHistory->last();
        $monthlyPeriodKeys = $monthlyPeriods->map(fn ($period) => $period->format('Y-m'))->values();
        $nextMonthlyKey = $monthlyPeriodKeys->first(fn ($monthKey) => !in_array($monthKey, $paidMonthKeys, true));
        $lockedMonthKeys = $nextMonthlyKey ? $monthlyPeriodKeys->skipUntil(fn ($monthKey) => $monthKey === $nextMonthlyKey)->skip(1)->all() : [];
        $administrativeFeeAlreadyPaid = $selectedEnrollment && !empty($paidAdminMonthKeys);
        // Administrative fee is $10 for a full 12-month academic year; late enrollments only pay for the months remaining from their enrollment month.
        $administrativeStartMonthKey = $originalStartDate ? Carbon::parse($originalStartDate)->format('Y-m') : $monthlyPeriodKeys->first();
        $administrativeEligibleMonthKeys = $monthlyPeriodKeys->filter(fn ($monthKey) => $administrativeStartMonthKey === null || $monthKey >= $administrativeStartMonthKey)->values();
        $khmerMonthNames = [1 => 'មករា', 2 => 'កុម្ភៈ', 3 => 'មីនា', 4 => 'មេសា', 5 => 'ឧសភា', 6 => 'មិថុនា', 7 => 'កក្កដា', 8 => 'សីហា', 9 => 'កញ្ញា', 10 => 'តុលា', 11 => 'វិច្ឆិកា', 12 => 'ធ្នូ'];

        return view('payments.create', compact('students', 'academicYears', 'selectedStudentId', 'selectedInvoiceId', 'selectedEnrollmentId', 'enrollments', 'selectedEnrollment', 'selectedAcademicYear', 'monthlyTuitionFee', 'services', 'monthlyPeriods', 'paidMonthKeys', 'paidAdminMonthKeys', 'lockedMonthKeys', 'administrativeFeeAlreadyPaid', 'administrativeEligibleMonthKeys', 'khmerMonthNames', 'originalStartDate', 'lastPayment'));
    }

    public function store(StorePaymentRequest $request)
    {
        $data = $request->validated();
        $student = Student::findOrFail($data['student_id']);
        $enrollment = app(PaymentEligibilityService::class)->resolve($request->user(), $student, (int) $data['enrollment_id']);
        $data['academic_year_id'] = $enrollment->academic_year_id;
        $data['branch_id'] = $enrollment->branch_id;
        $payment = FinanceService::recordPayment($data, $request->user()->id);
        return redirect()->route('payments.show', array_filter(['payment' => $payment->id, 'print' => $request->boolean('save_and_print') ? 1 : null]))->with('success', 'បានរក្សាទុកការបង់ប្រាក់ និងវិក្កយបត្រដោយជោគជ័យ។');
    }

    public function show(Payment $payment)
    {
        $this->authorizeBranch($payment->branch_id);
        $payment->load(['student', 'branch', 'enrollment.grade', 'enrollment.schoolClass.grade', 'enrollment.schoolClass.shift', 'enrollment.academicYear', 'service', 'items', 'paymentMonths', 'invoice', 'invoiceDocument', 'refunds', 'journalEntry.lines.account', 'creator']);
        $settlement = PaymentSettlementService::summary($payment);
        $credit = $payment->student_id ? (float) StudentCredit::where('student_id', $payment->student_id)->where('currency', $payment->currency)->where('status', 'available')->get()->sum(fn ($item) => $item->available_amount) : 0;
        return view('payments.show', ['payment' => $payment, 'settlement' => $settlement, 'credit' => $credit, 'amountInWords' => \App\Services\KhmerAmountService::money((float) $payment->total_amount, $payment->currency, null)]);
    }

    public function receipt(Payment $payment)
    {
        $this->authorizeBranch($payment->branch_id);
        $payment->load(['student', 'enrollment.schoolClass', 'enrollment.academicYear', 'invoice', 'creator']);
        return view('payments.receipt', ['payment' => $payment, 'summary' => PaymentSettlementService::summary($payment)]);
    }

    public function refundReceipt(Refund $refund)
    {
        $refund->load(['payment.student', 'payment.invoice', 'approver', 'processor']);
        $this->authorizeBranch($refund->payment->branch_id);
        return view('payments.refund-receipt', compact('refund'));
    }

    public function edit(Payment $payment)
    {
        $this->authorizeBranch($payment->branch_id);
        $payment->load(['student', 'enrollment.schoolClass', 'enrollment.academicYear', 'items', 'paymentMonths', 'invoice']);
        return view('payments.edit', compact('payment'));
    }

    public function refundCreate(Payment $payment)
    {
        $this->authorizeBranch($payment->branch_id);
        $payment->load(['student', 'invoice', 'refunds']);
        $summary = PaymentSettlementService::summary($payment);
        return view('payments.refund', compact('payment', 'summary'));
    }

    public function refundStore(Request $request, Payment $payment)
    {
        $this->authorizeBranch($payment->branch_id);
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'gt:0'],
            'reason' => ['required', 'string', 'max:255'],
            'refund_method' => ['required', 'in:cash,bank,qr'],
            'note' => ['nullable', 'string', 'max:1000'],
        ], ['reason.required' => 'សូមបញ្ចូលមូលហេតុសងប្រាក់។']);
        PaymentSettlementService::refund($payment, $data, $request->user()->id);
        return redirect()->route('payments.show', $payment)->with('success', 'បានកត់ត្រាការសងប្រាក់ដោយជោគជ័យ។');
    }

    public function update(Request $request, Payment $payment)
    {
        $this->authorizeBranch($payment->branch_id);
        $data = $request->validate([
            'payment_date' => ['required', 'date'],
            'paid_until' => ['nullable', 'date', 'after_or_equal:payment_date'],
            'next_payment_date' => ['nullable', 'date'],
            'discount_type' => ['nullable', 'in:percent,fixed'],
            'discount_amount' => ['nullable', 'numeric', 'gte:0'],
            'payment_method' => ['required', 'in:cash,bank,qr,other'],
            'other_bank_name' => ['nullable', 'string', 'max:100', 'required_if:payment_method,other'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'payment_date.required' => 'សូមបញ្ចូលកាលបរិច្ឆេទបង់ប្រាក់។',
            'discount_amount.gte' => 'ចំនួនបញ្ចុះតម្លៃមិនត្រឹមត្រូវ។',
        ]);

        $multiplier = $payment->currency === 'KHR' && (float) $payment->exchange_rate > 0 ? (float) $payment->exchange_rate : 1;
        $tuition = (float) $payment->tuition_amount / $multiplier;
        $administrative = (float) $payment->administrative_fee / $multiplier;
        $services = $payment->items
            ->reject(fn ($item) => str_starts_with((string) $item->description, 'Tuition') || str_contains(strtolower((string) $item->description), 'administrative'))
            ->sum(fn ($item) => (float) $item->amount) / $multiplier;
        $discount = (float) ($data['discount_amount'] ?? 0);
        if (($data['discount_type'] ?? null) === 'percent') $discount = round($tuition * $discount / 100, 2);
        abort_if($discount > $tuition, 422, 'ចំនួនបញ្ចុះតម្លៃមិនអាចលើសថ្លៃសិក្សាទេ។');
        $total = round(($tuition - $discount + $administrative + $services) * $multiplier, 2);
        abort_if($total <= 0, 422, 'ចំនួនទឹកប្រាក់ត្រូវតែធំជាងសូន្យ។');

        DB::transaction(function () use ($payment, $data, $discount, $total, $multiplier) {
            $payment->update([
                ...$data,
                'discount_amount' => round($discount * $multiplier, 2),
                'total_amount' => $total,
            ]);
            $payment->invoice?->update(['total_amount' => $total, 'currency' => $payment->currency]);
            $payment->journalEntry?->lines()->each(function ($line) use ($total) {
                $line->update(['debit' => (float) $line->debit > 0 ? $total : 0, 'credit' => (float) $line->credit > 0 ? $total : 0]);
            });
        });

        return redirect()->route('payments.show', $payment)->with('success', 'បានកែប្រែការបង់ប្រាក់ដោយជោគជ័យ។');
    }

    public function destroy(Payment $payment)
    {
        $this->authorizeBranch($payment->branch_id);
        DB::transaction(function () use ($payment) {
            $payment->load(['invoice', 'items', 'paymentMonths', 'journalEntry.lines']);
            $payment->journalEntry?->lines()->delete();
            $payment->journalEntry?->delete();
            $payment->invoice?->delete();
            $payment->items()->delete();
            $payment->paymentMonths()->delete();
            $payment->delete();
        });

        return redirect()->route('payments.index')->with('success', 'ការបង់ប្រាក់ត្រូវបានលុបដោយជោគជ័យ។');
    }

    private function authorizeBranch(int $branchId): void
    {
        abort_unless(auth()->user()->isSuperAdmin() || auth()->user()->branch_id === $branchId, 403);
    }
}
