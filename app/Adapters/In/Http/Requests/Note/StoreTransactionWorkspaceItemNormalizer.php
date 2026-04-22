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

            $normalized = [
                'entry_mode' => self::trimOrNull($item['entry_mode'] ?? null),
                'description' => self::trimOrNull($item['description'] ?? null),
                'part_source' => self::trimOrNull($item['part_source'] ?? null),
                'service' => StoreTransactionWorkspaceServiceNormalizer::normalize($item['service'] ?? []),
                'product_lines' => [StoreTransactionWorkspaceProductLineNormalizer::normalize($item['product_lines'] ?? [])],
                'external_purchase_lines' => [StoreTransactionWorkspaceExternalPurchaseLineNormalizer::normalize($item['external_purchase_lines'] ?? [])],
            ];

            if (self::isMeaningfulItem($normalized)) {
                $items[] = $normalized;
            }
        }

        return $items;
    }

    /**
     * @param array<string, mixed> $item
     */
    private static function isMeaningfulItem(array $item): bool
    {
        if (($item['entry_mode'] ?? null) !== null) {
            return true;
        }

        if (($item['description'] ?? null) !== null) {
            return true;
        }

        if (($item['part_source'] ?? null) !== null) {
            return true;
        }

        $service = is_array($item['service'] ?? null) ? $item['service'] : [];
        foreach (['name', 'price_rupiah', 'notes'] as $key) {
            if (($service[$key] ?? null) !== null) {
                return true;
            }
        }

        $productLine = is_array(($item['product_lines'][0] ?? null)) ? $item['product_lines'][0] : [];
        foreach (['product_id', 'qty', 'unit_price_rupiah'] as $key) {
            if (($productLine[$key] ?? null) !== null) {
                return true;
            }
        }

        $externalLine = is_array(($item['external_purchase_lines'][0] ?? null)) ? $item['external_purchase_lines'][0] : [];
        foreach (['label', 'qty', 'unit_cost_rupiah'] as $key) {
            if (($externalLine[$key] ?? null) !== null) {
                return true;
            }
        }

        return false;
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
