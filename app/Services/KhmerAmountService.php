<?php

namespace App\Services;

class KhmerAmountService
{
    private const DIGITS = ['', 'មួយ', 'ពីរ', 'បី', 'បួន', 'ប្រាំ', 'ប្រាំមួយ', 'ប្រាំពីរ', 'ប្រាំបី', 'ប្រាំបួន'];
    private const TENS = ['', '', 'ម្ភៃ', 'សាមសិប', 'សែសិប', 'ហាសិប', 'ហុកសិប', 'ចិតសិប', 'ប៉ែតសិប', 'កៅសិប'];

    public static function money(float $amount, string $currency, ?float $exchangeRate = null): string
    {
        if ($currency === 'KHR') {
            if ($exchangeRate && $exchangeRate > 0) {
                $amount = round($amount * $exchangeRate);
            }
            return self::integer((int) $amount) . ' រៀល';
        }

        $amount = round($amount, 2);
        $whole = (int) floor($amount);
        $cents = (int) round(($amount - $whole) * 100);
        $words = self::integer($whole) . ' ដុល្លារអាមេរិក';

        return $cents > 0 ? $words . ' និង ' . self::integer($cents) . ' សេន' : $words . 'គត់';
    }

    public static function integer(int $number): string
    {
        if ($number === 0) {
            return 'សូន្យ';
        }
        if ($number < 0) {
            return 'ដក' . self::integer(abs($number));
        }
        if ($number >= 1000000) {
            return self::integer(intdiv($number, 1000000)) . 'លាន' . self::remainder($number % 1000000);
        }
        if ($number >= 100000) {
            return self::integer(intdiv($number, 100000)) . 'សែន' . self::remainder($number % 100000);
        }
        if ($number >= 10000) {
            return self::integer(intdiv($number, 10000)) . 'ម៉ឺន' . self::remainder($number % 10000);
        }
        if ($number >= 1000) {
            return self::integer(intdiv($number, 1000)) . 'ពាន់' . self::remainder($number % 1000);
        }
        if ($number >= 100) {
            return self::integer(intdiv($number, 100)) . 'រយ' . self::remainder($number % 100);
        }
        if ($number >= 10) {
            $tens = intdiv($number, 10);
            $units = $number % 10;
            return ($tens === 1 ? 'ដប់' : self::TENS[$tens]) . ($units ? self::DIGITS[$units] : '');
        }

        return self::DIGITS[$number];
    }

    private static function remainder(int $number): string
    {
        return $number > 0 ? self::integer($number) : '';
    }
}
