<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Requests\Note;

final class StoreTransactionWorkspaceGrandTotalCalculator
{
    public static function calculate(mixed $items): int
    {
        if (! is_array($items)) {
            return 0;
        }

        $total = 0;

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $entryMode = (string) ($item['entry_mode'] ?? '');
            $partSource = (string) ($item['part_source'] ?? 'none');

            if ($entryMode === 'product') {
                $total += self::productLineTotal($item['product_lines'] ?? []);
                continue;
            }

            if ($entryMode !== 'service') {
                continue;
            }

            if (self::usesPackageAutoSplit($item, $partSource)) {
                $total += self::intValue($item['package_total_rupiah'] ?? null);
                continue;
            }

            $servicePrice = self::intValue($item['service']['price_rupiah'] ?? null);

            if ($partSource === 'store_stock') {
                $total += $servicePrice + self::productLineTotal($item['product_lines'] ?? []);
                continue;
            }

            if ($partSource === 'external_purchase') {
                $total += $servicePrice + self::externalLineTotal($item['external_purchase_lines'] ?? []);
                continue;
            }

            $total += $servicePrice;
        }

        return $total;
    }

    /**
     * @param array<string, mixed> $item
     */
    private static function usesPackageAutoSplit(array $item, string $partSource): bool
    {
        if (! in_array($partSource, ['store_stock', 'external_purchase'], true)) {
            return false;
        }

        return ($item['pricing_mode'] ?? null) === 'package_auto_split'
            && self::intValue($item['package_total_rupiah'] ?? null) > 0;
    }

    private static function productLineTotal(mixed $lines): int
    {
        $total = 0;

        foreach (self::rawProductLines($lines) as $line) {
            $total += self::intValue($line['qty'] ?? null)
                * self::intValue($line['unit_price_rupiah'] ?? null);
        }

        return $total;
    }

    private static function externalLineTotal(mixed $lines): int
    {
        $line = self::firstLine($lines);

        return self::intValue($line['qty'] ?? null) * self::intValue($line['unit_cost_rupiah'] ?? null);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function rawProductLines(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $rawLines = self::looksLikeProductLine($value) ? [$value] : array_values($value);
        $lines = [];

        foreach ($rawLines as $line) {
            if (is_array($line)) {
                $lines[] = $line;
            }
        }

        return $lines;
    }

    /**
     * @param array<mixed> $value
     */
    private static function looksLikeProductLine(array $value): bool
    {
        return array_key_exists('product_id', $value)
            || array_key_exists('qty', $value)
            || array_key_exists('unit_price_rupiah', $value)
            || array_key_exists('price_basis', $value);
    }

    /**
     * @return array<string, mixed>
     */
    private static function firstLine(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $first = array_values($value)[0] ?? [];

        return is_array($first) ? $first : [];
    }

    private static function intValue(mixed $value): int
    {
        return is_int($value) ? $value : 0;
    }
}
