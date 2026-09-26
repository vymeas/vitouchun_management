<?php

namespace App\Services;

use App\Models\StudentCard;

class StudentCardStatusService
{
    public static function current(StudentCard $card): string
    {
        if (in_array($card->status, ['inactive', 'lost', 'replaced'], true)) return $card->status;
        return $card->expiry_date?->isPast() ? 'expired' : 'active';
    }

    public static function sync(StudentCard $card): string
    {
        $status = self::current($card);
        if ($status !== $card->status && $card->status === 'active') $card->update(['status' => $status]);
        return $status;
    }
}
