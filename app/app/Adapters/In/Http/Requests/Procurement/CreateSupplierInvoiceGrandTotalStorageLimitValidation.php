<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Requests\Procurement;

use Illuminate\Validation\Validator;

final class CreateSupplierInvoiceGrandTotalStorageLimitValidation
{
    private const MAX_INVOICE_GRAND_TOTAL_RUPIAH = 2147483647;

    /**
     * @param array<int, mixed> $lines
     */
    public function validate(array $lines, Validator $validator): void
    {
        $grandTotal = 0;

        foreach ($lines as $index => $line) {
            if (! is_array($line)) {
                continue;
            }

            $lineTotal = isset($line['line_total_rupiah']) ? (int) $line['line_total_rupiah'] : 0;

            if ($lineTotal < 1) {
                continue;
            }

            if ($lineTotal > self::MAX_INVOICE_GRAND_TOTAL_RUPIAH) {
                $validator->errors()->add(
                    'lines.' . $index . '.line_total_rupiah',
                    'Total rincian melebihi batas penyimpanan sistem.'
                );

                return;
            }

            $grandTotal += $lineTotal;

            if ($grandTotal > self::MAX_INVOICE_GRAND_TOTAL_RUPIAH) {
                $validator->errors()->add(
                    'supplier_invoice',
                    'Total keseluruhan nota melebihi batas penyimpanan sistem. Kurangi total rincian lalu simpan lagi.'
                );

                return;
            }
        }
    }
}
