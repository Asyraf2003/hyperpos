<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Requests\Note;

final class StoreTransactionWorkspaceServiceNormalizer
{
    /**
     * @param mixed $value
     * @return array<string, mixed>
     */
    public static function normalize(mixed $value): array
    {
        $service = is_array($value) ? $value : [];

        return [
            'name' => self::trimOrNull($service['name'] ?? null),
            'price_rupiah' => self::integerOrNull($service['price_rupiah'] ?? null),
            'notes' => self::trimOrNull($service['notes'] ?? null),
        ];
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
