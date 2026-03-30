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
        $line = self::firstLine($item['product_lines'] ?? []);

        if (self::blank($line['product_id'] ?? null)) {
            $validator->errors()->add("items.$index.product_lines.0.product_id", 'Product wajib dipilih.');
        }

        if (self::intValue($line['qty'] ?? null) <= 0) {
            $validator->errors()->add("items.$index.product_lines.0.qty", 'Qty produk wajib lebih dari 0.');
        }

        if (self::intValue($line['unit_price_rupiah'] ?? null) <= 0) {
            $validator->errors()->add("items.$index.product_lines.0.unit_price_rupiah", 'Harga satuan produk wajib lebih dari 0.');
        }
    }

    /**
     * @param mixed $value
     * @return array<string, mixed>
     */
    private static function firstLine(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $first = array_values($value)[0] ?? [];

        return is_array($first) ? $first : [];
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
