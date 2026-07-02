<?php

declare(strict_types=1);

namespace App\Support;

use Throwable;

final class ViewDateFormatter
{
    public static function display(mixed $value, bool $withTime = false): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        $text = (string) $value;

        try {
            $date = ViewDateValueParser::parse($value, $text);

            if ($date === null) {
                return $text;
            }

            if (self::shouldUseDisplayTimezone($value, $text, $withTime)) {
                $date = $date->setTimezone(self::displayTimezone());
            }

            return IndonesianDateLabelFormatter::format($date, $withTime);
        } catch (Throwable) {
            return $text;
        }
    }

    public static function range(mixed $from, mixed $to): string
    {
        return self::display($from) . ' s/d ' . self::display($to);
    }

    /**
     * @return array{label: string, value: string}
     */
    public static function reportPeriodContext(mixed $from, mixed $to): array
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
            'value' => self::range($from, $to),
        ];
    }

    public static function reportPeriodLabel(mixed $from, mixed $to): string
    {
        return self::reportPeriodContext($from, $to)['label'];
    }

    public static function reportPeriodValue(mixed $from, mixed $to): string
    {
        return self::reportPeriodContext($from, $to)['value'];
    }

    private static function dateOnly(mixed $value): ?\Illuminate\Support\Carbon
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

    private static function monthYear(\Illuminate\Support\Carbon $date): string
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

    private static function shouldUseDisplayTimezone(mixed $value, string $text, bool $withTime): bool
    {
        if (! $withTime) {
            return false;
        }

        if ($value instanceof \DateTimeInterface) {
            return true;
        }

        $trimmed = trim($text);

        if (preg_match('/^\d{2}\/\d{2}\/\d{4}(?:\s+\d{2}:\d{2}(?::\d{2})?)?$/', $trimmed) === 1) {
            return false;
        }

        return preg_match('/\d{1,2}:\d{2}/', $trimmed) === 1;
    }

    private static function displayTimezone(): string
    {
        if (function_exists('config')) {
            try {
                $configured = config('app.display_timezone');
            } catch (Throwable) {
                $configured = null;
            }

            if (is_string($configured) && trim($configured) !== '') {
                return trim($configured);
            }
        }

        return 'Asia/Makassar';
    }
}
