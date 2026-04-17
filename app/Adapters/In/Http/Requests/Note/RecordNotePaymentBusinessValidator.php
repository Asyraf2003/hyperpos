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

        if (! is_array($selectedRowIds) || $selectedRowIds === []) {
            $validator->errors()->add('selected_row_ids', 'Minimal satu line open harus dipilih.');
        }

        if (($payload['amount_paid'] ?? null) === null) {
            $validator->errors()->add('amount_paid', 'Nominal pembayaran wajib diisi.');
        }

        if (($payload['payment_method'] ?? null) === 'cash' && ($payload['amount_received'] ?? null) === null) {
            $validator->errors()->add('amount_received', 'Uang masuk wajib diisi untuk pembayaran cash.');
        }
    }
}
