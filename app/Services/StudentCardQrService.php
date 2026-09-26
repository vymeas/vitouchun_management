<?php

namespace App\Services;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\SvgWriter;

class StudentCardQrService
{
    public static function svg(string $url): string
    {
        return (new Builder(writer: new SvgWriter(), data: $url, size: 220, margin: 8))->build()->getString();
    }
}
