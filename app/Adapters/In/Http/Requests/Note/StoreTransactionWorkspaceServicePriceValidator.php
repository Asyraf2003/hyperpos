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

        return false;
    }

    private static function intValue(mixed $value): int
    {
        return is_int($value) ? $value : 0;
    }
}
