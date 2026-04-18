<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Requests\Procurement;

use Illuminate\Validation\Validator;

final class CreateSupplierInvoiceDuplicateProductPostValidation
{
    /**
     * @param array<int, mixed> $lines
     */
    public function validate(array $lines, Validator $validator): void
    {
        $seen = [];

        foreach ($lines as $index => $line) {
            if (! is_array($line)) {
                continue;
            }

            $productId = isset($line['product_id']) ? trim((string) $line['product_id']) : '';
            if ($productId === '') {
                continue;
            }

            $lineNo = isset($line['line_no']) ? (int) $line['line_no'] : ((int) $index + 1);

            if (array_key_exists($productId, $seen)) {
                $firstLineNo = $seen[$productId];

                $validator->errors()->add(
                    'lines.' . $index . '.product_id',
                    'Baris ' . $lineNo . ': produk yang sama sudah dipakai di baris ' . $firstLineNo . '.'
                );

                continue;
            }

            $seen[$productId] = $lineNo;
        }
    }
}
