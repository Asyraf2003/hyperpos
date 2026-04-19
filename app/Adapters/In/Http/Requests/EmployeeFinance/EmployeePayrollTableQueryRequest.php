<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Requests\EmployeeFinance;

use Illuminate\Foundation\Http\FormRequest;

final class EmployeePayrollTableQueryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'in:10'],
        ];
    }
}
