<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Requests\ProductCatalog;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class ProductTableQueryRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'q' => $this->trimOrNull('q'),
            'merek' => $this->trimOrNull('merek'),
            'sort_by' => $this->trimOrNull('sort_by'),
            'sort_dir' => $this->trimOrNull('sort_dir'),
        ]);
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'in:10'],
            'sort_by' => ['nullable', 'in:nama_barang,merek,ukuran,harga_jual,stok_saat_ini'],
            'sort_dir' => ['nullable', 'in:asc,desc'],
            'merek' => ['nullable', 'string'],
            'ukuran_min' => ['nullable', 'integer', 'min:0'],
            'ukuran_max' => ['nullable', 'integer', 'min:0'],
            'harga_min' => ['nullable', 'integer', 'min:0'],
            'harga_max' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(fn (Validator $v) => $this->validateRanges($v));
    }

    private function validateRanges(Validator $validator): void
    {
        $ukuranMin = $this->input('ukuran_min');
        $ukuranMax = $this->input('ukuran_max');
        $hargaMin = $this->input('harga_min');
        $hargaMax = $this->input('harga_max');

        if ($ukuranMin !== null && $ukuranMax !== null && (int) $ukuranMin > (int) $ukuranMax) {
            $validator->errors()->add('ukuran_min', 'Ukuran minimum tidak boleh lebih besar dari ukuran maksimum.');
        }

        if ($hargaMin !== null && $hargaMax !== null && (int) $hargaMin > (int) $hargaMax) {
            $validator->errors()->add('harga_min', 'Harga minimum tidak boleh lebih besar dari harga maksimum.');
        }
    }

    private function trimOrNull(string $key): ?string
    {
        $value = $this->input($key);

        if (! is_string($value)) return null;

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }
}
