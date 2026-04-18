<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Requests\Procurement;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class CreateSupplierInvoicePostValidator
{
    public function validate(FormRequest $request, Validator $validator): void
    {
        (new CreateSupplierInvoiceDatePostValidation())->validate($request, $validator);
        (new CreateSupplierInvoiceLinesPostValidation())->validate($request, $validator);
    }
}
