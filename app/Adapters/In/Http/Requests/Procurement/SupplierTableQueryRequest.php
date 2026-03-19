<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Requests\Procurement;

use Illuminate\Foundation\Http\FormRequest;

final class SupplierTableQueryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'q' => $this->trimOrNull('q'),
            'sort_by' => $this->trimOrNull('sort_by'),
            'sort_dir' => $this->trimOrNull('sort_dir'),
        ]);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'in:10'],
            'sort_by' => ['nullable', 'in:nama_pt_pengirim,invoice_count,outstanding_rupiah,invoice_unpaid_count,last_shipment_date'],
            'sort_dir' => ['nullable', 'in:asc,desc'],
        ];
    }

    private function trimOrNull(string $key): ?string
    {
        $value = $this->input($key);

        if (! is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }
}