<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Requests\Note;

final class RecordNotePaymentInputNormalizer
{
    /**
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    public static function normalize(array $input): array
    {
        return [
            'selected_row_ids' => self::normalizeSelectedRowIds($input['selected_row_ids'] ?? []),
            'payment_scope' => self::normalizeString($input['payment_scope'] ?? null),
            'payment_method' => self::normalizePaymentMethod($input['payment_method'] ?? null),
            'paid_at' => self::normalizeString($input['paid_at'] ?? null),
            'amount_paid' => self::normalizeInteger($input['amount_paid'] ?? null),
            'amount_received' => self::normalizeInteger($input['amount_received'] ?? null),
        ];
    }

    /**
     * @param mixed $value
     * @return list<string>
     */
    private static function normalizeSelectedRowIds(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $normalized = [];

        foreach ($value as $item) {
            if (! is_string($item)) {
                continue;
            }

            $trimmed = trim($item);

            if ($trimmed !== '') {
                $normalized[] = $trimmed;
            }
        }

        return $normalized;
    }

    private static function normalizeString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    private static function normalizePaymentMethod(mixed $value): ?string
    {
        $method = self::normalizeString($value);

        if ($method === 'transfer') {
            return 'tf';
        }

        return $method;
    }

    private static function normalizeInteger(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value;
        }

        if (! is_string($value)) {
            return null;
        }

        $cleaned = preg_replace('/[^0-9]/', '', $value);

        if (! is_string($cleaned) || $cleaned === '') {
            return null;
        }

        return (int) $cleaned;
    }
}
