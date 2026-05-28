<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Requests\Note;

final class StoreTransactionWorkspaceProductLineNormalizer
{
    /**
     * @param mixed $value
     * @return array<string, mixed>
     */
    public static function normalize(mixed $value): array
    {
        return self::normalizeMany($value)[0] ?? self::emptyLine();
    }

    /**
     * @param mixed $value
     * @return list<array<string, mixed>>
     */
    public static function normalizeMany(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $rawLines = self::looksLikeLine($value) ? [$value] : array_values($value);
        $lines = [];

        foreach ($rawLines as $line) {
            if (! is_array($line)) {
                continue;
            }

            $normalized = [
                'product_id' => self::trimOrNull($line['product_id'] ?? null),
                'qty' => self::integerOrNull($line['qty'] ?? null),
                'unit_price_rupiah' => self::integerOrNull($line['unit_price_rupiah'] ?? null),
                'price_basis' => self::trimOrNull($line['price_basis'] ?? null),
            ];

            if (self::hasAnyValue($normalized)) {
                $lines[] = $normalized;
            }
        }

        return $lines;
    }

    /**
     * @return array<string, mixed>
     */
    private static function emptyLine(): array
    {
        return [
            'product_id' => null,
            'qty' => null,
            'unit_price_rupiah' => null,
            'price_basis' => null,
        ];
    }

    /**
     * @param array<mixed> $value
     */
    private static function looksLikeLine(array $value): bool
    {
        return array_key_exists('product_id', $value)
            || array_key_exists('qty', $value)
            || array_key_exists('unit_price_rupiah', $value)
            || array_key_exists('price_basis', $value);
    }

    /**
     * @param array<string, mixed> $line
     */
    private static function hasAnyValue(array $line): bool
    {
        foreach (['product_id', 'qty', 'unit_price_rupiah', 'price_basis'] as $key) {
            if (($line[$key] ?? null) !== null) {
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
