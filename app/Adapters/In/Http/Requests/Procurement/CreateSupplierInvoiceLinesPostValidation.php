<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Requests\Procurement;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class CreateSupplierInvoiceLinesPostValidation
{
    public function validate(FormRequest $request, Validator $validator): void
    {
        $lines = $request->input('lines');

        if (! is_array($lines)) {
            return;
        }

        $this->validateDuplicateLineNo($lines, $validator);
        (new CreateSupplierInvoiceDuplicateProductPostValidation())->validate($lines, $validator);
        (new CreateSupplierInvoiceGrandTotalStorageLimitValidation())->validate($lines, $validator);
    }

    /**
     * @param array<int, mixed> $lines
     */
    private function validateDuplicateLineNo(array $lines, Validator $validator): void
    {
        $seen = [];

        foreach ($lines as $index => $line) {
            if (! is_array($line)) {
                continue;
            }

            $lineNo = $line['line_no'] ?? null;
            if ($lineNo === null || $lineNo === '') {
                continue;
            }

            $normalized = (string) $lineNo;

            if (array_key_exists($normalized, $seen)) {
                $validator->errors()->add(
                    'lines.' . $index . '.line_no',
                    'Nomor baris pada rincian tidak boleh duplikat.'
                );
            }

            $seen[$normalized] = true;
        }
    }

}
