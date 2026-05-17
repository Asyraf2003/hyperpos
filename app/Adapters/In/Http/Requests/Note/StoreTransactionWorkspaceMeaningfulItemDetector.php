<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Requests\Note;

final class StoreTransactionWorkspaceMeaningfulItemDetector
{
    /**
     * @param array<string, mixed> $item
     */
    public static function detect(array $item): bool
    {
        foreach (['entry_mode', 'description', 'part_source', 'pricing_mode', 'package_total_rupiah'] as $key) {
            if (($item[$key] ?? null) !== null) {
                return true;
            }
        }

        if (self::hasAnyValue($item['service'] ?? [], ['name', 'price_rupiah', 'notes'])) {
            return true;
        }

        if (self::hasAnyValue(self::firstLine($item['product_lines'] ?? []), [
            'product_id',
            'qty',
            'unit_price_rupiah',
        ])) {
            return true;
        }

        return self::hasAnyValue(self::firstLine($item['external_purchase_lines'] ?? []), [
            'label',
            'qty',
            'unit_cost_rupiah',
        ]);
    }

    /**
     * @param mixed $value
     * @param list<string> $keys
     */
    private static function hasAnyValue(mixed $value, array $keys): bool
    {
        if (! is_array($value)) {
            return false;
        }

        foreach ($keys as $key) {
            if (($value[$key] ?? null) !== null) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param mixed $value
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
}
