<?php

namespace App\Services;

use Carbon\CarbonPeriod;
use App\Models\Account;
use App\Models\Expense;
use App\Models\Enrollment;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\PaymentMonth;
use App\Models\Service;
use App\Models\StudentCredit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use RuntimeException;

class FinanceService
{
    public static function recordPayment(array $data, int $userId): Payment
    {
        return DB::transaction(function () use ($data, $userId) {
            $data['currency'] = (float) ($data['exchange_rate'] ?? 0) > 0 ? 'KHR' : 'USD';
            $branchId = (int) $data['branch_id'];
            $enrollment = Enrollment::whereKey($data['enrollment_id'])
                ->where('student_id', $data['student_id'])
                ->where('branch_id', $branchId)
                ->where('academic_year_id', $data['academic_year_id'])
                ->where('status', 'active')
                ->whereNotNull('grade_id')
                ->whereNotNull('class_id')
                ->firstOrFail();

            $selectedMonths = array_values(array_unique($data['selected_months'] ?? []));
            $administrativeMonths = array_values(array_unique($data['administrative_months'] ?? []));
            if ($selectedMonths && !$enrollment) {
                abort(422, 'សូមជ្រើសរើសការចុះឈ្មោះសម្រាប់ខែដែលត្រូវបង់ប្រាក់។');
            }
            if ($administrativeMonths && !$enrollment) {
                abort(422, 'សូមជ្រើសរើសការចុះឈ្មោះសម្រាប់សេវារដ្ឋបាល។');
            }
            $academicYear = $enrollment->academicYear;
            $periodKeys = collect(CarbonPeriod::create(Carbon::parse($academicYear->start_date)->startOfMonth(), '1 month', Carbon::parse($academicYear->end_date)->startOfMonth()))
                ->map(fn ($period) => $period->format('Y-m'))
                ->values();
            $paidTuitionMonthKeys = PaymentMonth::where('enrollment_id', $enrollment->id)
                ->where('academic_year_id', $data['academic_year_id'])
                ->where('type', 'tuition')
                ->pluck('month_key')
                ->all();
            $selectedMonths = collect($selectedMonths)
                ->sortBy(fn ($monthKey) => $periodKeys->search($monthKey))
                ->values()
                ->all();
            $expectedMonthlySequence = $periodKeys
                ->reject(fn ($monthKey) => in_array($monthKey, $paidTuitionMonthKeys, true))
                ->take(count($selectedMonths))
                ->values()
                ->all();
            abort_if(array_diff($selectedMonths, $periodKeys->all()), 422, 'ខែដែលបានជ្រើសរើសមិនស្ថិតក្នុងឆ្នាំសិក្សាទេ។');
            abort_if($selectedMonths && $selectedMonths !== $expectedMonthlySequence, 422, 'សូមជ្រើសរើសខែតាមលំដាប់ ដោយមិនអាចរំលងខែបានទេ។');
            if ($selectedMonths && PaymentMonth::where('enrollment_id', $enrollment->id)->where('academic_year_id', $data['academic_year_id'])->where('type', 'tuition')->whereIn('month_key', $selectedMonths)->exists()) {
                abort(422, 'ខែដែលបានជ្រើសរើសមានការបង់ប្រាក់រួចហើយ។');
            }
            $annualAdministrativeFeePaid = Payment::where('enrollment_id', $enrollment->id)
                ->where('academic_year_id', $data['academic_year_id'])
                ->where('administrative_fee', '>', 0)
                ->exists()
                || PaymentMonth::where('enrollment_id', $enrollment->id)
                    ->where('academic_year_id', $data['academic_year_id'])
                    ->where('type', 'administrative')
                    ->exists();
            abort_if(($administrativeMonths || (float) ($data['administrative_fee'] ?? 0) > 0) && $annualAdministrativeFeePaid, 422, 'ថ្លៃសេវារដ្ឋបាលប្រចាំឆ្នាំបានបង់រួចហើយ។');
            if ($administrativeMonths && PaymentMonth::where('enrollment_id', $enrollment->id)->where('academic_year_id', $data['academic_year_id'])->where('type', 'administrative')->whereIn('month_key', $administrativeMonths)->exists()) {
                abort(422, 'ខែសេវារដ្ឋបាលដែលបានជ្រើសរើសមានការបង់ប្រាក់រួចហើយ។');
            }

            $monthlyFee = (float) ($data['monthly_fee'] ?? $enrollment->grade?->monthly_tuition_fee ?? 0);
            $tuitionCalculation = app(TuitionPaymentCalculationService::class)->calculate($enrollment, $selectedMonths, $monthlyFee, $data['payment_date'] ?? null);
            $data['paid_until'] = $tuitionCalculation['paid_until']?->toDateString();
            $data['next_payment_date'] = $tuitionCalculation['next_payment_date']?->toDateString();
            $tuitionAmount = $selectedMonths ? $tuitionCalculation['tuition_amount'] : (float) ($data['tuition_amount'] ?? 0);
            $serviceAmount = 0;
            $lineItems = collect($data['line_items'] ?? [])
                ->filter(fn ($item) => ($item['description'] ?? '') !== '' && (float) ($item['amount'] ?? 0) > 0)
                ->map(fn ($item) => ['description' => $item['description'], 'amount' => round((float) $item['amount'], 2)])
                ->values();
            if (!empty($data['service_id'])) {
                $service = Service::where('branch_id', $branchId)->active()->findOrFail($data['service_id']);
                $serviceAmount = $service->name_kh === 'គ្មាន' ? 0 : (float) ($data['service_amount'] ?? $service->price);
                $lineItems = $serviceAmount > 0 ? collect([['description' => $service->name_kh, 'amount' => $serviceAmount]]) : collect();
            }
            if (empty($data['service_id'])) {
                $serviceAmount = (float) $lineItems->sum('amount');
            }
            $administrativeFee = $administrativeMonths ? round(10 * count($administrativeMonths) / 12, 2) : (float) ($data['administrative_fee'] ?? 0);
            $tuitionSubtotal = round($tuitionAmount, 2);
            $discountAmount = (float) ($data['discount_amount'] ?? 0);
            if (($data['discount_type'] ?? null) === 'percent') {
                $discountAmount = round($tuitionSubtotal * $discountAmount / 100, 2);
            }
            abort_if($discountAmount > $tuitionSubtotal, 422, 'ចំនួនបញ្ចុះតម្លៃមិនអាចលើសថ្លៃសិក្សាទេ។');
            $netTuition = round($tuitionSubtotal - $discountAmount, 2);
            $subtotal = round($netTuition + $administrativeFee + $serviceAmount, 2);
            $currencyMultiplier = $data['currency'] === 'KHR' && (float) ($data['exchange_rate'] ?? 0) > 0 ? (float) $data['exchange_rate'] : 1;
            $storedTuitionAmount = round($tuitionAmount * $currencyMultiplier, 2);
            $storedAdministrativeFee = round($administrativeFee * $currencyMultiplier, 2);
            $storedLineItems = $lineItems->map(fn ($item) => ['description' => $item['description'], 'amount' => round($item['amount'] * $currencyMultiplier, 2)]);
            $storedDiscountAmount = round($discountAmount * $currencyMultiplier, 2);
            $receivedAmount = round((float) ($data['received_amount'] ?? $subtotal) * $currencyMultiplier, 2);
            $total = round($subtotal * $currencyMultiplier, 2);
            abort_if($total <= 0, 422, 'ចំនួនទឹកប្រាក់ត្រូវតែធំជាងសូន្យ។');

            $payment = Payment::create([
                ...$data,
                'enrollment_id' => $enrollment?->id,
                'reference' => self::nextPaymentReference(),
                'created_by' => $userId,
                'tuition_amount' => $storedTuitionAmount,
                'administrative_fee' => $storedAdministrativeFee,
                'discount_amount' => $storedDiscountAmount,
                'received_amount' => $receivedAmount,
                'other_bank_name' => $data['other_bank_name'] ?? null,
                'total_amount' => $total,
                'status' => 'posted',
            ]);

            if ($enrollment) {
                $enrollment->update(['status' => 'active']);
                $enrollment->student()->update(['status' => 'active']);
                foreach ($selectedMonths as $monthKey) {
                    PaymentMonth::create(['payment_id' => $payment->id, 'enrollment_id' => $enrollment->id, 'academic_year_id' => $data['academic_year_id'], 'month_key' => $monthKey, 'type' => 'tuition', 'month_start' => Carbon::createFromFormat('Y-m', $monthKey)->startOfMonth()]);
                }
                foreach ($administrativeMonths as $monthKey) {
                    PaymentMonth::create(['payment_id' => $payment->id, 'enrollment_id' => $enrollment->id, 'academic_year_id' => $data['academic_year_id'], 'month_key' => $monthKey, 'type' => 'administrative', 'month_start' => Carbon::createFromFormat('Y-m', $monthKey)->startOfMonth()]);
                }

                $remainingToAllocate = $receivedAmount;
                $tuitionMonthRows = $payment->paymentMonths()->where('type', 'tuition')->orderBy('month_start')->get();
                $tuitionPerMonth = $tuitionMonthRows->count() > 0 ? round($storedTuitionAmount / $tuitionMonthRows->count(), 2) : 0;
                foreach ($tuitionMonthRows as $paymentMonth) {
                    $allocation = min($remainingToAllocate, $tuitionPerMonth);
                    if ($allocation <= 0) break;
                    PaymentAllocation::create(['payment_id' => $payment->id, 'payment_month_id' => $paymentMonth->id, 'amount' => $allocation, 'currency' => $data['currency']]);
                    $remainingToAllocate = round($remainingToAllocate - $allocation, 2);
                }
                if ($remainingToAllocate > 0) {
                    $credit = StudentCredit::create(['student_id' => $payment->student_id, 'enrollment_id' => $enrollment->id, 'amount' => $remainingToAllocate, 'currency' => $data['currency'], 'source_payment_id' => $payment->id, 'note' => 'ឥណទានពីការបង់ប្រាក់លើស']);
                    AuditService::log('payment.credit_created', 'បង្កើតឥណទានពីការបង់ប្រាក់លើស', $credit, [], $credit->toArray());
                }
            }

            if ($payment->student_id) {
                $payment->student()->update(['status' => 'active']);
            }

            if ($tuitionAmount > 0) {
                if ($selectedMonths) {
                    foreach ($selectedMonths as $monthKey) {
                        $payment->items()->create(['description' => 'Tuition - ' . $monthKey, 'amount' => round($storedTuitionAmount / count($selectedMonths), 2)]);
                    }
                } else {
                    $payment->items()->create(['description' => 'Tuition', 'amount' => $storedTuitionAmount]);
                }
            }
            if ($administrativeFee > 0) {
                $payment->items()->create(['description' => 'Administrative fee', 'amount' => $storedAdministrativeFee]);
            }
            foreach ($storedLineItems as $lineItem) {
                $payment->items()->create($lineItem);
            }

            $invoice = !empty($data['invoice_id'])
                ? Invoice::where('branch_id', $branchId)->findOrFail($data['invoice_id'])
                : Invoice::create([
                    'invoice_number' => self::nextReference('INV', Invoice::class, 'invoice_number'),
                    'payment_id' => $payment->id,
                    'branch_id' => $branchId,
                    'total_amount' => $total,
                    'currency' => $data['currency'],
                    'status' => 'paid',
                ]);

            $cashAccount = self::accountForMethod($branchId, $data['payment_method']);
            $incomeAccount = self::account($branchId, '4100', 'Tuition Income', 'income');
            self::balancedEntry(
                $branchId,
                $userId,
                $payment->payment_date,
                $payment->reference,
                'Student payment ' . $payment->reference,
                $payment,
                [['account_id' => $cashAccount->id, 'debit' => $total, 'credit' => 0]],
                [['account_id' => $incomeAccount->id, 'debit' => 0, 'credit' => $total]],
            );

            AuditService::log('payment.created', 'កត់ត្រាការបង់ប្រាក់: ' . $payment->reference, $payment, [], $payment->toArray());

            return $payment->load(['student', 'items', 'invoice', 'paymentMonths']);
        });
    }

    public static function recordExpense(array $data, int $userId): Expense
    {
        return DB::transaction(function () use ($data, $userId) {
            $amount = round((float) $data['amount'], 2);
            abort_if($amount <= 0, 422, 'ចំនួនចំណាយត្រូវតែធំជាងសូន្យ។');
            $expense = Expense::create([
                ...$data,
                'reference' => self::nextReference('EXP', Expense::class),
                'created_by' => $userId,
                'approved_by' => $userId,
                'status' => 'approved',
                'amount' => $amount,
            ]);

            $cashAccount = self::accountForMethod($expense->branch_id, $expense->payment_method);
            $expenseAccount = self::account($expense->branch_id, '5100', $expense->category, 'expense');
            self::balancedEntry(
                $expense->branch_id,
                $userId,
                $expense->expense_date,
                $expense->reference,
                $expense->description,
                $expense,
                [['account_id' => $expenseAccount->id, 'debit' => $amount, 'credit' => 0]],
                [['account_id' => $cashAccount->id, 'debit' => 0, 'credit' => $amount]],
            );

            AuditService::log('expense.created', 'កត់ត្រាចំណាយ: ' . $expense->reference, $expense, [], $expense->toArray());
            return $expense;
        });
    }

    public static function account(int $branchId, string $code, string $name, string $type): Account
    {
        return Account::firstOrCreate(['branch_id' => $branchId, 'code' => $code], ['name' => $name, 'type' => $type, 'is_active' => true]);
    }

    private static function accountForMethod(int $branchId, string $method): Account
    {
        return match ($method) {
            'bank' => self::account($branchId, '1200', 'Bank', 'asset'),
            'qr' => self::account($branchId, '1300', 'QR / Digital Wallet', 'asset'),
            default => self::account($branchId, '1100', 'Cash', 'asset'),
        };
    }

    private static function balancedEntry(int $branchId, int $userId, $date, string $reference, string $description, object $source, array $debits, array $credits): JournalEntry
    {
        $debitTotal = round(array_sum(array_column($debits, 'debit')), 2);
        $creditTotal = round(array_sum(array_column($credits, 'credit')), 2);
        if ($debitTotal !== $creditTotal) {
            throw new RuntimeException('Unbalanced journal entry.');
        }

        $entry = JournalEntry::create([
            'reference' => 'JE-' . $reference,
            'branch_id' => $branchId,
            'created_by' => $userId,
            'entry_date' => $date,
            'description' => $description,
            'source_type' => $source::class,
            'source_id' => $source->id,
            'status' => 'posted',
        ]);
        foreach ([...$debits, ...$credits] as $line) {
            $entry->lines()->create($line);
        }
        return $entry;
    }

    private static function nextReference(string $prefix, string $model, string $column = 'reference'): string
    {
        $number = $model::query()->where($column, 'like', $prefix . '-%')->count() + 1;
        do {
            $reference = sprintf('%s-%s-%06d', $prefix, now()->format('Y'), $number++);
        } while ($model::where($column, $reference)->exists());
        return $reference;
    }

    private static function nextPaymentReference(): string
    {
        $last = Payment::where('reference', 'like', 'TF%')->lockForUpdate()->orderByDesc('id')->first();
        $number = 1;
        if ($last && preg_match('/(\d+)$/', $last->reference, $matches)) {
            $number = (int) $matches[1] + 1;
        }
        do {
            $reference = 'TF' . str_pad((string) $number++, 6, '0', STR_PAD_LEFT);
        } while (Payment::where('reference', $reference)->exists());
        return $reference;
    }
}
