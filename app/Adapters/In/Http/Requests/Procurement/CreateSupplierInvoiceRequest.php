<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Requests\Procurement;

use Illuminate\Foundation\Http\FormRequest;

final class CreateSupplierInvoiceRequest extends FormRequest
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
            'nama_pt_pengirim' => ['required', 'string'],
            'tanggal_pengiriman' => ['required', 'date_format:Y-m-d'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.product_id' => ['required', 'string'],
            'lines.*.qty_pcs' => ['required', 'integer', 'min:1'],
            'lines.*.line_total_rupiah' => ['required', 'integer', 'min:1'],
        ];
    }
}
