<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Requests\Procurement;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class CreateSupplierInvoicePostValidator
{
    public function validate(FormRequest $request, Validator $validator): void
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
}
