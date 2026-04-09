<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Requests\Procurement;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class UpdateSupplierInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $normalizer = new CreateSupplierInvoiceInputNormalizer();
        $this->merge($normalizer->normalize($this->all()));
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'nomor_faktur' => ['required', 'string'],
            'nama_pt_pengirim' => ['required', 'string'],
            'tanggal_pengiriman' => ['required', 'date_format:Y-m-d'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.line_no' => ['required', 'integer', 'min:1'],
            'lines.*.product_id' => ['required', 'string'],
            'lines.*.qty_pcs' => ['required', 'integer', 'min:1'],
            'lines.*.line_total_rupiah' => ['required', 'integer', 'min:1'],
        ];
    }

    public function withValidator($validator): void
    {
        $postValidator = new CreateSupplierInvoicePostValidator();
        $validator->after(fn (Validator $v) => $postValidator->validate($this, $v));
    }
}
