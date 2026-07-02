<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Carbon;
use Throwable;

final class ReportPeriodDateLabelFormatter
{
    /**
     * @return array{label: string, value: string}
     */
    public static function context(mixed $from, mixed $to): array
    {
        $fromDate = self::dateOnly($from);
        $toDate = self::dateOnly($to);

        if (
            $fromDate !== null
            && $toDate !== null
            && $fromDate->format('Y-m') === $toDate->format('Y-m')
        ) {
            return [
                'label' => 'Bulan Terkait',
                'value' => self::monthYear($fromDate),
            ];
        }

        return [
            'label' => 'Rentang Tanggal',
            'value' => ViewDateFormatter::range($from, $to),
        ];
    }

    public static function label(mixed $from, mixed $to): string
    {
        return self::context($from, $to)['label'];
    }

    public static function value(mixed $from, mixed $to): string
    {
        return self::context($from, $to)['value'];
    }

    private static function dateOnly(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        $text = (string) $value;

        try {
            return ViewDateValueParser::parse($value, $text);
        } catch (Throwable) {
            return null;
        }
    }

    private static function monthYear(Carbon $date): string
    {
        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        $month = $months[(int) $date->format('n')] ?? $date->format('m');

        return $month . ' ' . $date->format('Y');
    }
}
