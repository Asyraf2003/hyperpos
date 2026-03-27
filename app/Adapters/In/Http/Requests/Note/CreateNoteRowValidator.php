<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Requests\Note;

use Illuminate\Validation\Validator;

final class CreateNoteRowValidator
{
    public function validate(mixed $rows, Validator $validator): void
    {
        if (! is_array($rows)) {
            return;
        }

        foreach ($rows as $index => $row) {
            if (! is_array($row)) {
                continue;
            }

            $lineType = $row['line_type'] ?? null;

            if ($lineType === 'product') {
                $this->validateProductRow($index, $row, $validator);
            }

            if ($lineType === 'service') {
                $this->validateServiceRow($index, $row, $validator);
            }
        }
    }

    /**
     * @param array<string, mixed> $row
     */
    private function validateProductRow(int|string $index, array $row, Validator $validator): void
    {
        if (($row['product_id'] ?? null) === null) {
            $validator->errors()->add(
                "rows.$index.product_id",
                'Produk wajib dipilih untuk baris bertipe Produk.'
            );
        }

        if (($row['qty'] ?? null) === null) {
            $validator->errors()->add(
                "rows.$index.qty",
                'Qty wajib diisi untuk baris bertipe Produk.'
            );
        }
    }

    /**
     * @param array<string, mixed> $row
     */
    private function validateServiceRow(int|string $index, array $row, Validator $validator): void
    {
        if (($row['service_name'] ?? null) === null) {
            $validator->errors()->add(
                "rows.$index.service_name",
                'Nama servis wajib diisi untuk baris bertipe Servis.'
            );
        }

        if (($row['service_price_rupiah'] ?? null) === null) {
            $validator->errors()->add(
                "rows.$index.service_price_rupiah",
                'Harga servis wajib diisi untuk baris bertipe Servis.'
            );
        }
    }
}
