<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Requests\Note;

final class StoreTransactionWorkspaceServicePriceValidator
{
    /**
     * @param array<string, mixed> $item
     */
    public static function isValid(array $item): bool
    {
        $service = is_array($item['service'] ?? null) ? $item['service'] : [];
        $price = self::intValue($service['price_rupiah'] ?? null);

        if ($price > 0) {
            return true;
        }

        return $price === 0
            && ($item['pricing_mode'] ?? null) === 'package_auto_split'
            && self::intValue($item['package_total_rupiah'] ?? null) > 0
            && self::hasStoreStockLine($item);
    }

    /**
     * @param array<string, mixed> $item
     */
    private static function hasStoreStockLine(array $item): bool
    {
        $line = self::firstLine($item['product_lines'] ?? []);

        return is_string($line['product_id'] ?? null)
            && trim((string) $line['product_id']) !== ''
            && self::intValue($line['qty'] ?? null) > 0
            && self::intValue($line['unit_price_rupiah'] ?? null) > 0;
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

    private static function intValue(mixed $value): int
    {
        return is_int($value) ? $value : 0;
    }
}
