<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Requests\Procurement;

use Illuminate\Foundation\Http\FormRequest;

final class ReceiveSupplierInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'tanggal_terima' => ['required', 'date_format:Y-m-d'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.supplier_invoice_line_id' => ['required', 'string'],
            'lines.*.qty_diterima' => ['required', 'integer', 'min:1'],
        ];
    }
}
