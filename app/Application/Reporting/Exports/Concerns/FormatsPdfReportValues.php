<?php

declare(strict_types=1);

namespace App\Application\Reporting\Exports\Concerns;

use Carbon\CarbonImmutable;
use Throwable;

trait FormatsPdfReportValues
{
    private function formatRange(string $from, string $to): string
    {
        return $this->formatDate($from).' s/d '.$this->formatDate($to);
    }

    private function formatDate(string $value): string
    {
        if ($value === '') {
            return '-';
        }

        try {
            return CarbonImmutable::parse($value)->format('d/m/Y');
        } catch (Throwable) {
            return $value;
        }
    }

    private function rupiah(mixed $value): string
    {
        return 'Rp '.number_format($this->integerValue($value), 0, ',', '.');
    }

    private function nullableIntegerValue(mixed $value): string|int
    {
        if ($value === null || $value === '') {
            return '-';
        }

        return $this->integerValue($value);
    }

    private function integerValue(mixed $value): int
    {
        return is_numeric($value) ? (int) $value : 0;
    }

    private function nullableString(mixed $value): string
    {
        return is_string($value) && $value !== '' ? $value : '-';
    }

    private function stringValue(mixed $value): string
    {
        return is_string($value) ? $value : '';
    }
}
