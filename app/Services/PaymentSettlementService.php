<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Refund;
use Illuminate\Support\Facades\DB;

class PaymentSettlementService
{
    public static function summary(Payment $payment): array
    {
        $received = (float) ($payment->received_amount ?? $payment->total_amount);
        $refunded = (float) $payment->refunds()->where('status', 'completed')->sum('amount');
        $netPaid = round($received - $refunded, 2);
        $due = (float) $payment->total_amount;
        return [
            'due' => round($due, 2),
            'paid' => round($received, 2),
            'refunded' => round($refunded, 2),
            'net_paid' => $netPaid,
            'outstanding' => max(0, round($due - $netPaid, 2)),
            'overpayment' => max(0, round($netPaid - $due, 2)),
            'refundable' => max(0, round($received - $refunded, 2)),
            'status' => $netPaid < $due ? 'partial' : ($netPaid > $due ? 'overpaid' : 'paid'),
        ];
    }

    public static function refund(Payment $payment, array $data, int $userId): Refund
    {
        return DB::transaction(function () use ($payment, $data, $userId) {
            $summary = self::summary($payment);
            $amount = round((float) $data['amount'], 2);
            abort_if($amount <= 0 || $amount > $summary['refundable'], 422, 'ចំនួនសងប្រាក់មិនអាចលើសចំនួនប្រាក់ដែលអាចសងបានទេ។');
            $number = Refund::query()->count() + 1;
            do {
                $refundNo = sprintf('REF-%s-%06d', now()->format('Y'), $number++);
            } while (Refund::where('refund_no', $refundNo)->exists());
            $refund = Refund::create([
                ...$data,
                'payment_id' => $payment->id,
                'refund_no' => $refundNo,
                'currency' => $payment->currency,
                'refund_date' => $data['refund_date'] ?? now()->toDateString(),
                'status' => 'completed',
                'approved_by' => $userId,
                'processed_by' => $userId,
            ]);
            AuditService::log('payment.refunded', 'សងប្រាក់: ' . $refund->refund_no, $refund, [], $refund->toArray());
            return $refund;
        });
    }
}