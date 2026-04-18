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
        if (($payload['payment_method'] ?? null) === 'cash' && ($payload['amount_received'] ?? null) === null) {
            $validator->errors()->add('amount_received', 'Uang masuk wajib diisi untuk pembayaran cash.');
        }
    }
}
