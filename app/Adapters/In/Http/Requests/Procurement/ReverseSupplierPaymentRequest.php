<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Requests\Procurement;

use Closure;
use Illuminate\Foundation\Http\FormRequest;

final class ReverseSupplierPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reason' => [
                'required',
                'string',
                static function (string $attribute, mixed $value, Closure $fail): void {
                    if (trim((string) $value) === '') {
                        $fail('Alasan reversal pembayaran supplier wajib diisi.');
                    }
                },
            ],
        ];
    }
}
