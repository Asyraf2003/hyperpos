<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Carbon;
use Throwable;

final class ViewDateFormatter
{
    public static function display(mixed $value, bool $withTime = false): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        $text = (string) $value;

        if (preg_match('/^\d{2}\/\d{2}\/\d{4}/', $text) === 1) {
            return $text;
        }

        try {
            return Carbon::parse($value)->format($withTime ? 'd/m/Y H:i' : 'd/m/Y');
        } catch (Throwable) {
            return $text;
        }
    }
}
