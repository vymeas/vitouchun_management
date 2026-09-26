<?php

namespace App\Services;

class ScoreCalculationService
{
    public static function grade(float $score, float $maximum = 100): array
    {
        $percentage = $maximum > 0 ? round(($score / $maximum) * 100, 2) : 0;
        $grade = match (true) {
            $percentage >= 90 => 'A',
            $percentage >= 80 => 'B',
            $percentage >= 70 => 'C',
            $percentage >= 60 => 'D',
            $percentage >= 50 => 'E',
            default => 'F',
        };
        return ['grade' => $grade, 'result' => $percentage >= 50 ? 'passed' : 'failed', 'percentage' => $percentage];
    }
}
