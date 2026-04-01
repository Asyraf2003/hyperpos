<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Requests\Note;

final class UpdateTransactionWorkspaceInputNormalizer
{
    /**
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    public static function normalize(array $input): array
    {
        return [
            'note' => StoreTransactionWorkspaceNoteNormalizer::normalize($input['note'] ?? []),
            'items' => StoreTransactionWorkspaceItemNormalizer::normalizeMany($input['items'] ?? []),
        ];
    }
}
