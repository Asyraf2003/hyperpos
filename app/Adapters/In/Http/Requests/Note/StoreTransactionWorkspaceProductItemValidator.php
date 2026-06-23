<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Requests\Note;

use Illuminate\Validation\Validator;

final class StoreTransactionWorkspaceProductItemValidator
{
    /**
     * @param array<string, mixed> $item
     */
    public static function validate(array $item, int $index, Validator $validator): void
    {
        $lines = self::lines($item['product_lines'] ?? []);
        $line = $lines[0] ?? [];

        if (self::blank($line['product_id'] ?? null)) {
            $validator->errors()->add("items.$index.product_lines.0.product_id", 'Product wajib dipilih.');
        }

        if (self::intValue($line['qty'] ?? null) <= 0) {
            $validator->errors()->add("items.$index.product_lines.0.qty", 'Qty produk wajib lebih dari 0.');
        }

        if (self::intValue($line['unit_price_rupiah'] ?? null) <= 0) {
            $validator->errors()->add("items.$index.product_lines.0.unit_price_rupiah", 'Harga satuan produk wajib lebih dari 0.');
        }

        if (count($lines) > 3) {
            $validator->errors()->add("items.$index.product_lines", 'Paket servis maksimal memakai 3 produk.');
        }

        foreach ($lines as $lineIndex => $productLine) {
            if ($lineIndex === 0) {
                continue;
            }

            if (self::blank($productLine['product_id'] ?? null)) {
                $validator->errors()->add("items.$index.product_lines.$lineIndex.product_id", 'Product wajib dipilih.');
            }

            if (self::intValue($productLine['qty'] ?? null) <= 0) {
                $validator->errors()->add("items.$index.product_lines.$lineIndex.qty", 'Qty produk wajib lebih dari 0.');
            }

            if (self::intValue($productLine['unit_price_rupiah'] ?? null) <= 0) {
                $validator->errors()->add("items.$index.product_lines.$lineIndex.unit_price_rupiah", 'Harga satuan produk wajib lebih dari 0.');
            }
        }
    }

    /**
     * @param mixed $value
     * @return list<array<string, mixed>>
     */
    private static function lines(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $lines = [];

        foreach ($value as $candidate) {
            if (! is_array($candidate)) {
                continue;
            }

            $line = [];

            foreach ($candidate as $key => $lineValue) {
                if (is_string($key)) {
                    $line[$key] = $lineValue;
                }
            }

            $lines[] = $line;
        }

        return $lines;
    }

    private static function blank(mixed $value): bool
    {
        return ! is_string($value) || trim($value) === '';
    }

    private static function intValue(mixed $value): int
    {
        return is_int($value) ? $value : 0;
    }
}
