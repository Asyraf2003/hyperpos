<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Requests\Note;

final class StoreTransactionWorkspaceProductLineShape
{
    /**
     * @param array<mixed> $value
     * @return list<array<string, mixed>>
     */
    public static function rawLines(array $value): array
    {
        $rawLines = self::looksLikeLine($value) ? [$value] : array_values($value);
        $lines = [];

        foreach ($rawLines as $line) {
            if (is_array($line)) {
                $lines[] = $line;
            }
        }

        return $lines;
    }

    /**
     * @param array<mixed> $value
     */
    private static function looksLikeLine(array $value): bool
    {
        return array_key_exists('product_id', $value)
            || array_key_exists('qty', $value)
            || array_key_exists('unit_price_rupiah', $value)
            || array_key_exists('price_basis', $value);
    }
}
