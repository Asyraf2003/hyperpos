<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

final class CreateTransactionWorkspaceProductLineCollection
{
    /**
     * @param mixed $value
     * @return list<array<string, mixed>>
     */
    public function lines(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        if ($this->looksLikeLine($value)) {
            return [$value];
        }

        $lines = [];

        foreach (array_values($value) as $line) {
            if (is_array($line)) {
                $lines[] = $line;
            }
        }

        return $lines;
    }

    /**
     * @param array<mixed> $value
     */
    private function looksLikeLine(array $value): bool
    {
        return array_key_exists('product_id', $value)
            || array_key_exists('qty', $value)
            || array_key_exists('unit_price_rupiah', $value)
            || array_key_exists('price_basis', $value);
    }
}
