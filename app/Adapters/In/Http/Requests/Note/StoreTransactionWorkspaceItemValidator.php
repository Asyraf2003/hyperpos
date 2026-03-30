<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Requests\Note;

use Illuminate\Validation\Validator;

final class StoreTransactionWorkspaceItemValidator
{
    /**
     * @param array<string, mixed> $item
     */
    public static function validate(array $item, int $index, Validator $validator): void
    {
        $entryMode = (string) ($item['entry_mode'] ?? '');
        $partSource = (string) ($item['part_source'] ?? 'none');

        if ($entryMode === 'product') {
            StoreTransactionWorkspaceProductItemValidator::validate($item, $index, $validator);
            return;
        }

        if ($entryMode === 'service') {
            StoreTransactionWorkspaceServiceItemValidator::validate($item, $partSource, $index, $validator);
            return;
        }

        $validator->errors()->add("items.$index.entry_mode", 'Tipe item workspace tidak valid.');
    }
}
