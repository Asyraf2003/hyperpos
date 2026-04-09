<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Requests\Procurement;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class CreateSupplierInvoicePostValidator
{
    public function validate(FormRequest $request, Validator $validator): void
    {
        $this->validateTanggalTerima($request, $validator);
        $this->validateDuplicateLineNo($request, $validator);
    }

    private function validateTanggalTerima(FormRequest $request, Validator $validator): void
    {
        $autoReceive = $request->input('auto_receive');
        $tanggalTerima = $request->input('tanggal_terima');
        $tanggalPengiriman = $request->input('tanggal_pengiriman');

        if ($autoReceive !== true || $tanggalTerima === null) {
            return;
        }

        if ((string) $tanggalTerima < (string) $tanggalPengiriman) {
            $validator->errors()->add(
                'tanggal_terima',
                'Tanggal terima tidak boleh lebih awal dari tanggal pengiriman.'
            );
        }
    }

    private function validateDuplicateLineNo(FormRequest $request, Validator $validator): void
    {
        $lines = $request->input('lines');

        if (! is_array($lines)) {
            return;
        }

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
