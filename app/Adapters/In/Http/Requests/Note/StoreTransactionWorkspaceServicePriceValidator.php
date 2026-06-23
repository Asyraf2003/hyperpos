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

        if ($price !== 0 || ($item['pricing_mode'] ?? null) !== 'package_auto_split') {
            return false;
        }

        return self::legacyPackageServiceTotal($item) > 0;
    }

    /**
     * @param array<string, mixed> $item
     */
    private static function legacyPackageServiceTotal(array $item): int
    {
        $packageTotal = self::intValue($item['package_total_rupiah'] ?? null);

        if ($packageTotal <= 0) {
            return 0;
        }

        $sparepartTotal = 0;
        $lines = is_array($item['product_lines'] ?? null) ? $item['product_lines'] : [];

        foreach ($lines as $line) {
            if (! is_array($line)) {
                continue;
            }

            $sparepartTotal += self::intValue($line['qty'] ?? null)
                * self::intValue($line['unit_price_rupiah'] ?? null);
        }

        return $packageTotal - $sparepartTotal;
    }

    private static function intValue(mixed $value): int
    {
        return is_int($value) ? $value : 0;
    }
}
