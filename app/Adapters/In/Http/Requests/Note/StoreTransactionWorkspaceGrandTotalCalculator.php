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

    private static function productLineTotal(mixed $lines): int
    {
        $line = self::firstLine($lines);

        return self::intValue($line['qty'] ?? null) * self::intValue($line['unit_price_rupiah'] ?? null);
    }

    private static function externalLineTotal(mixed $lines): int
    {
        $line = self::firstLine($lines);

        return self::intValue($line['qty'] ?? null) * self::intValue($line['unit_cost_rupiah'] ?? null);
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
