<?php

namespace App\Services;

use Illuminate\Support\Collection;

class AttendanceCalculationService
{
    public static function summary(Collection $records): array
    {
        $counts = $records->groupBy('status')->map->count();
        $present = (int) ($counts['present'] ?? 0);
        $late = (int) ($counts['late'] ?? 0);
        $absent = (int) ($counts['absent'] ?? 0);
        $excused = (int) ($counts['excused'] ?? 0);
        $total = $present + $late + $absent + $excused;
        return ['total' => $total, 'present' => $present, 'late' => $late, 'absent' => $absent, 'excused' => $excused, 'rate' => $total ? round((($present + $late) / $total) * 100, 2) : 0];
    }
}
