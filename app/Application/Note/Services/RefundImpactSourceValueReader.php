<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

final class RefundImpactSourceValueReader
{
    public function stringFrom(mixed $source, array $keys, array $methods): string
    {
        foreach ($methods as $method) {
            if (is_object($source) && method_exists($source, $method)) {
                return trim((string) $source->{$method}());
            }
        }

        foreach ($keys as $key) {
            if (is_array($source) && array_key_exists($key, $source)) {
                return trim((string) $source[$key]);
            }
        }

        return '';
    }

    public function intFrom(mixed $source, array $keys, array $methods): int
    {
        foreach ($methods as $method) {
            if (is_object($source) && method_exists($source, $method)) {
                return (int) $source->{$method}();
            }
        }

        foreach ($keys as $key) {
            if (is_array($source) && array_key_exists($key, $source)) {
                return (int) $source[$key];
            }
        }

        return 0;
    }

    public function amountFrom(mixed $source, int $qty): int
    {
        if (is_object($source) && method_exists($source, 'lineTotalRupiah')) {
            return (int) $source->lineTotalRupiah()->amount();
        }

        if (is_array($source) && array_key_exists('line_total_rupiah', $source)) {
            return (int) $source['line_total_rupiah'];
        }

        if (is_object($source) && method_exists($source, 'unitCostRupiah')) {
            return (int) $source->unitCostRupiah()->amount() * $qty;
        }

        if (is_array($source) && array_key_exists('unit_cost_rupiah', $source)) {
            return (int) $source['unit_cost_rupiah'] * $qty;
        }

        return 0;
    }
}
