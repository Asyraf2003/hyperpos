<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Requests\Note;

final class StoreTransactionWorkspacePaymentNormalizer
{
    /**
     * @param mixed $value
     * @return array<string, mixed>
     */
    public static function normalize(mixed $value): array
    {
        $payment = is_array($value) ? $value : [];

        return [
            'decision' => self::trimOrNull($payment['decision'] ?? 'skip') ?? 'skip',
            'payment_method' => self::trimOrNull($payment['payment_method'] ?? null),
            'paid_at' => self::trimOrNull($payment['paid_at'] ?? null),
            'amount_paid_rupiah' => self::integerOrNull($payment['amount_paid_rupiah'] ?? null),
            'amount_received_rupiah' => self::integerOrNull($payment['amount_received_rupiah'] ?? null),
            'notes' => self::trimOrNull($payment['notes'] ?? null),
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
