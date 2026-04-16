<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Requests\ProductCatalog;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateProductRequest extends FormRequest
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
            'kode_barang' => ['nullable', 'string'],
            'nama_barang' => ['required', 'string'],
            'merek' => ['required', 'string'],
            'ukuran' => ['nullable', 'integer'],
            'harga_jual' => ['required', 'integer', 'min:1'],
            'reorder_point_qty' => ['nullable', 'integer', 'min:0'],
            'critical_threshold_qty' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
