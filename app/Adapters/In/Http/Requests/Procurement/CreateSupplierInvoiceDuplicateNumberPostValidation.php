<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Requests\Procurement;

use App\Application\Procurement\Services\SupplierInvoiceDuplicateNumberChecker;
use Illuminate\Validation\Validator;

final class CreateSupplierInvoiceDuplicateNumberPostValidation
{
    public function __construct(
        private readonly SupplierInvoiceDuplicateNumberChecker $checker,
    ) {
    }

    public function validate(
        string $nomorFaktur,
        Validator $validator,
        ?string $excludeSupplierInvoiceId = null,
    ): void {
        $exists = $this->checker->exists($nomorFaktur, $excludeSupplierInvoiceId);

        if (! $exists) {
            return;
        }

        $validator->errors()->add(
            'nomor_faktur',
            'Nomor faktur sudah dipakai oleh nota supplier aktif.'
        );
    }
}
