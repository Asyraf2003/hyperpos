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

            $service = is_array($item['service'] ?? null) ? $item['service'] : [];

            $items[] = [
                'entry_mode' => self::trimOrNull($item['entry_mode'] ?? null),
                'description' => self::trimOrNull($item['description'] ?? null),
                'part_source' => self::trimOrNull($item['part_source'] ?? null),
                'service' => [
                    'name' => self::trimOrNull($service['name'] ?? null),
                    'price_rupiah' => self::integerOrNull($service['price_rupiah'] ?? null),
                    'notes' => self::trimOrNull($service['notes'] ?? null),
                ],
                'product_lines' => [self::normalizeProductLine($item['product_lines'] ?? [])],
                'external_purchase_lines' => [self::normalizeExternalLine($item['external_purchase_lines'] ?? [])],
            ];
        }

        return $items;
    }

    /**
     * @param mixed $value
     * @return array<string, mixed>
     */
    private static function normalizeProductLine(mixed $value): array
    {
        $line = self::firstArrayItem($value);

        return [
            'product_id' => self::trimOrNull($line['product_id'] ?? null),
            'qty' => self::integerOrNull($line['qty'] ?? null),
            'unit_price_rupiah' => self::integerOrNull($line['unit_price_rupiah'] ?? null),
        ];
    }

    /**
     * @param mixed $value
     * @return array<string, mixed>
     */
    private static function normalizeExternalLine(mixed $value): array
    {
        $line = self::firstArrayItem($value);

        return [
            'label' => self::trimOrNull($line['label'] ?? null),
            'qty' => self::integerOrNull($line['qty'] ?? null),
            'unit_cost_rupiah' => self::integerOrNull($line['unit_cost_rupiah'] ?? null),
        ];
    }

    /**
     * @param mixed $value
     * @return array<string, mixed>
     */
    private static function firstArrayItem(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $first = array_values($value)[0] ?? [];

        return is_array($first) ? $first : [];
    }

    private static function trimOrNull(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    private static function integerOrNull(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value;
        }

        if (! is_string($value)) {
            return null;
        }

        $cleaned = preg_replace('/[^0-9]/', '', $value);

        return is_string($cleaned) && $cleaned !== '' ? (int) $cleaned : null;
    }
}
