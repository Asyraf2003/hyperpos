<?php

declare(strict_types=1);

namespace App\Support;

use DateTimeImmutable;
use Illuminate\Support\Carbon;
use Throwable;

final class ViewDateFormatter
{
    /**
     * @var array<int, string>
     */
    private const INDONESIAN_MONTHS = [
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

    public static function display(mixed $value, bool $withTime = false): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        $text = (string) $value;

        try {
            $date = self::parseDisplayDate($value, $text);

            if ($date === null) {
                return $text;
            }

            return self::formatIndonesian($date, $withTime);
        } catch (Throwable) {
            return $text;
        }
    }

    public static function range(mixed $from, mixed $to): string
    {
        return self::display($from) . ' s/d ' . self::display($to);
    }

    private static function parseDisplayDate(mixed $value, string $text): ?Carbon
    {
        $trimmed = trim($text);

        if ($trimmed === '') {
            return null;
        }

        if (preg_match('/^\d{2}\/\d{2}\/\d{4}(?:\s+\d{2}:\d{2}(?::\d{2})?)?$/', $trimmed) === 1) {
            return self::parseSlashDate($trimmed);
        }

        return Carbon::parse($value);
    }

    private static function parseSlashDate(string $value): ?Carbon
    {
        foreach (['!d/m/Y H:i:s', '!d/m/Y H:i', '!d/m/Y'] as $format) {
            $parsed = DateTimeImmutable::createFromFormat($format, $value);
            $errors = DateTimeImmutable::getLastErrors();

            if (
                $parsed instanceof DateTimeImmutable
                && ($errors === false || (
                    (int) ($errors['warning_count'] ?? 0) === 0
                    && (int) ($errors['error_count'] ?? 0) === 0
                ))
            ) {
                return Carbon::instance($parsed);
            }
        }

        return null;
    }

    private static function formatIndonesian(Carbon $date, bool $withTime): string
    {
        $month = self::INDONESIAN_MONTHS[(int) $date->format('n')] ?? $date->format('m');

        $formatted = $date->format('d') . ' ' . $month . ' ' . $date->format('Y');

        if ($withTime) {
            $formatted .= $date->format(' H:i');
        }

        return $formatted;
    }
}
