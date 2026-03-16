<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Requests\EmployeeFinance;

use Illuminate\Foundation\Http\FormRequest;

class DisbursePayrollRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'required|uuid',
            'amount' => 'required|integer|min:1',
            'disbursement_date_string' => 'required|date_format:Y-m-d',
            'mode_value' => 'required|string|in:daily,weekly,monthly',
            'notes' => 'nullable|string',
        ];
    }
}
