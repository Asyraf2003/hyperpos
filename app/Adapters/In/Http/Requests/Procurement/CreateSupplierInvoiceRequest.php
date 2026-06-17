<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Requests\Procurement;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class CreateSupplierInvoiceRequest extends FormRequest
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

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'nomor_faktur' => ['required', 'string'],
            'nama_pt_pengirim' => ['required', 'string'],
            'tanggal_pengiriman' => ['required', 'date_format:Y-m-d'],
            'auto_receive' => ['nullable', 'boolean'],
            'tanggal_terima' => ['nullable', 'date_format:Y-m-d'],
            'tax_input' => ['nullable', 'string', 'max:64'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.line_no' => ['required', 'integer', 'min:1'],
            'lines.*.product_id' => ['required', 'string'],
            'lines.*.qty_pcs' => ['required', 'integer', 'min:1'],
            'lines.*.line_total_rupiah' => ['required', 'integer', 'min:1'],
            'lines.*.tax_input' => ['nullable', 'string', 'max:64'],
        ];
    }

    public function withValidator($validator): void
    {
        /** @var CreateSupplierInvoicePostValidator $postValidator */
        $postValidator = app(CreateSupplierInvoicePostValidator::class);
        $validator->after(fn (Validator $v) => $postValidator->validate($this, $v));
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return CreateSupplierInvoiceRequestText::messages();
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return CreateSupplierInvoiceRequestText::attributes();
    }
}
