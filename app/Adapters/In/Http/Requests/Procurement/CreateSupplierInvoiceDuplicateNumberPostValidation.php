<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Requests\Procurement;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Validator;

final class CreateSupplierInvoiceDuplicateNumberPostValidation
{
    public function validate(string $nomorFaktur, Validator $validator): void
    {
        $normalized = mb_strtolower(trim($nomorFaktur), 'UTF-8');

        if ($normalized === '') {
            return;
        }

        $exists = DB::table('supplier_invoices')
            ->where('nomor_faktur_normalized', $normalized)
            ->whereNull('voided_at')
            ->exists();

        if (! $exists) {
            return;
        }

        $validator->errors()->add(
            'nomor_faktur',
            'Nomor faktur sudah dipakai oleh nota supplier aktif.'
        );
    }
}
