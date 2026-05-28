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
        return self::normalizeMany($value)[0]
            ?? StoreTransactionWorkspaceProductLineListNormalizer::emptyLine();
    }

    /**
     * @param mixed $value
     * @return list<array<string, mixed>>
     */
    public static function normalizeMany(mixed $value): array
    {
        return StoreTransactionWorkspaceProductLineListNormalizer::normalizeMany($value);
    }
}
