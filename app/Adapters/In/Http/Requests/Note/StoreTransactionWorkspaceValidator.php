<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Requests\Note;

use Illuminate\Validation\Validator;

final class StoreTransactionWorkspaceValidator
{
    /**
     * @param array<string, mixed> $payload
     */
    public static function validate(array $payload, Validator $validator): void
    {
        $items = $payload['items'] ?? [];

        if (! is_array($items) || $items === []) {
            return;
        }

        foreach ($items as $index => $item) {
            if (! is_array($item)) {
                $validator->errors()->add("items.$index", 'Format item workspace tidak valid.');
                continue;
            }

            StoreTransactionWorkspaceItemValidator::validate($item, (int) $index, $validator);
        }

        StoreTransactionWorkspacePaymentValidator::validate($payload['inline_payment'] ?? [], $validator);
    }
}
