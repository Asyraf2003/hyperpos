<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Requests\Note;

final class StoreTransactionWorkspaceItemNormalizer
{
    /**
     * @param mixed $value
     * @return list<array<string, mixed>>
     */
    public static function normalizeMany(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $items = [];

        foreach ($value as $item) {
            if (! is_array($item)) {
                continue;
            }

            $items[] = [
                'entry_mode' => self::trimOrNull($item['entry_mode'] ?? null),
                'description' => self::trimOrNull($item['description'] ?? null),
                'part_source' => self::trimOrNull($item['part_source'] ?? null),
                'service' => StoreTransactionWorkspaceServiceNormalizer::normalize($item['service'] ?? []),
                'product_lines' => [StoreTransactionWorkspaceProductLineNormalizer::normalize($item['product_lines'] ?? [])],
                'external_purchase_lines' => [StoreTransactionWorkspaceExternalPurchaseLineNormalizer::normalize($item['external_purchase_lines'] ?? [])],
            ];
        }

        return $items;
    }

    private static function trimOrNull(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }
}
