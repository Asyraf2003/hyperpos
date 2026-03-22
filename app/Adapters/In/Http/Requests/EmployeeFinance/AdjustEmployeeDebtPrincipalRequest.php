<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Requests\EmployeeFinance;

use Illuminate\Foundation\Http\FormRequest;

final class AdjustEmployeeDebtPrincipalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'adjustment_type' => 'required|string|in:increase,decrease',
            'amount' => 'required|integer|min:1',
            'reason' => 'required|string',
        ];
    }
}
