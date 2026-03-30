<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Requests\Note;

use Illuminate\Validation\Validator;

final class RecordNotePaymentBusinessValidator
{
    /**
     * @param array<string, mixed> $payload
     */
    public static function validate(array $payload, Validator $validator): void
    {
        $selectedRowIds = $payload['selected_row_ids'] ?? [];

        if (is_array($selectedRowIds) && $selectedRowIds !== []) {
            self::validateLegacySelectedRowsFlow($payload, $validator);
            return;
        }

        self::validateNoteLevelFlow($payload, $validator);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private static function validateLegacySelectedRowsFlow(array $payload, Validator $validator): void
    {
        $selectedRowIds = $payload['selected_row_ids'] ?? [];

        if (! is_array($selectedRowIds) || $selectedRowIds === []) {
            $validator->errors()->add('selected_row_ids', 'Minimal satu baris harus dipilih.');
        }

        if (($payload['payment_method'] ?? null) === 'cash' && ($payload['amount_received'] ?? null) === null) {
            $validator->errors()->add('amount_received', 'Uang masuk wajib diisi untuk pembayaran cash.');
        }
    }

    /**
     * @param array<string, mixed> $payload
     */
    private static function validateNoteLevelFlow(array $payload, Validator $validator): void
    {
        $paymentScope = $payload['payment_scope'] ?? null;

        if (! is_string($paymentScope) || trim($paymentScope) === '') {
            $validator->errors()->add('payment_scope', 'payment scope wajib diisi.');
            return;
        }

        if ($paymentScope === 'partial' && ($payload['amount_paid'] ?? null) === null) {
            $validator->errors()->add('amount_paid', 'Nominal pembayaran wajib diisi untuk pembayaran sebagian.');
        }

        if (($payload['payment_method'] ?? null) === 'cash' && ($payload['amount_received'] ?? null) === null) {
            $validator->errors()->add('amount_received', 'Uang masuk wajib diisi untuk pembayaran cash.');
        }
    }
}
