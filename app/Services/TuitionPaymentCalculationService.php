<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\Payment;
use Illuminate\Support\Carbon;

class TuitionPaymentCalculationService
{
    public function calculate(Enrollment $enrollment, array $selectedMonths, float $monthlyFee, ?string $paymentDate = null): array
    {
        $selectedMonths = collect($selectedMonths)->unique()->sort()->values();
        $registrationDate = $enrollment->enrollment_date?->copy()->startOfDay();
        $history = $enrollment->payments()->orderBy('payment_date')->get(['payment_date', 'paid_until', 'next_payment_date']);
        $lastPayment = $history->last();
        $originalStartDate = $registrationDate ?: $history->first()?->payment_date?->copy()->startOfDay();
        $startDay = $originalStartDate?->day ?? 1;
        $prorated = 0;
        $fullMonths = 0;

        foreach ($selectedMonths as $monthKey) {
            $monthStart = Carbon::createFromFormat('Y-m', $monthKey)->startOfMonth();
            $isFirstRegistrationMonth = !$lastPayment && $originalStartDate && $monthStart->isSameMonth($originalStartDate);
            if ($isFirstRegistrationMonth && $startDay >= 15) {
                $remainingDays = $originalStartDate->daysInMonth - $startDay + 1;
                $dailyRate = round($monthlyFee / $originalStartDate->daysInMonth, 2);
                $prorated += round($dailyRate * $remainingDays, 2);
            } else {
                $fullMonths++;
            }
        }

        $tuitionAmount = round($prorated + ($fullMonths * $monthlyFee), 2);
        $lastSelectedMonth = $selectedMonths->last();
        $lastMonthEnd = $lastSelectedMonth ? Carbon::createFromFormat('Y-m', $lastSelectedMonth)->endOfMonth() : null;
        $nextPaymentDate = null;
        $paidUntil = null;
        if ($lastMonthEnd && $originalStartDate) {
            if ($startDay >= 15) {
                $nextPaymentDate = $lastMonthEnd->copy()->addDay();
            } else {
                $nextPaymentDate = $lastMonthEnd->copy()->addMonthNoOverflow()->day(min($startDay, $lastMonthEnd->copy()->addMonthNoOverflow()->daysInMonth));
            }
            $paidUntil = $nextPaymentDate->copy()->subDay();
        } elseif ($lastPayment) {
            $paidUntil = $lastPayment->paid_until;
            $nextPaymentDate = $lastPayment->next_payment_date;
        }

        return [
            'student_type' => $history->isEmpty() ? 'new' : 'existing',
            'registration_date' => $registrationDate,
            'original_start_date' => $originalStartDate,
            'payment_date' => $paymentDate ? Carbon::parse($paymentDate) : null,
            'monthly_tuition' => $monthlyFee,
            'prorated_days' => $prorated > 0 && $originalStartDate ? $originalStartDate->daysInMonth - $startDay + 1 : 0,
            'prorated_amount' => round($prorated, 2),
            'selected_months' => $selectedMonths->all(),
            'paid_until' => $paidUntil,
            'next_payment_date' => $nextPaymentDate,
            'tuition_amount' => $tuitionAmount,
        ];
    }
}