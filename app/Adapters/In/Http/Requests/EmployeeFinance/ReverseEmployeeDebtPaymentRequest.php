<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Requests\EmployeeFinance;

use Illuminate\Foundation\Http\FormRequest;

final class ReverseEmployeeDebtPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reason' => 'required|string',
        ];
    }
}
