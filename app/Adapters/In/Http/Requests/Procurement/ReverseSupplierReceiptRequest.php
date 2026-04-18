<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Requests\Procurement;

use Closure;
use Illuminate\Foundation\Http\FormRequest;

final class ReverseSupplierReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reversed_at' => ['required', 'date_format:Y-m-d'],
            'reason' => [
                'required',
                'string',
                static function (string $attribute, mixed $value, Closure $fail): void {
                    if (trim((string) $value) === '') {
                        $fail('Alasan reversal penerimaan supplier wajib diisi.');
                    }
                },
            ],
        ];
    }
}
