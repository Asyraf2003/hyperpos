<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Requests\Procurement;

use Illuminate\Foundation\Http\FormRequest;

final class VoidSupplierInvoiceRequest extends FormRequest
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
            'void_reason' => ['required', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'void_reason.required' => 'Alasan pembatalan wajib diisi.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $reason = $this->input('void_reason');

        if (! is_string($reason)) {
            return;
        }

        $trimmed = trim($reason);

        $this->merge([
            'void_reason' => $trimmed === '' ? null : $trimmed,
        ]);
    }
}
