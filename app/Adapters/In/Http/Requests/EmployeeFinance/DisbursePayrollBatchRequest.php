<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Requests\EmployeeFinance;

use Illuminate\Foundation\Http\FormRequest;

final class DisbursePayrollBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'disbursement_date_string' => ['required', 'date_format:Y-m-d'],
            'mode_value' => ['required', 'string', 'in:daily,weekly,monthly'],
            'notes' => ['nullable', 'string'],
            'rows' => ['required', 'array', 'min:1'],
            'rows.*.employee_id' => ['required', 'uuid'],
            'rows.*.amount' => ['required', 'integer', 'min:1'],
            'rows.*.mode_value_override' => ['nullable', 'string', 'in:daily,weekly,monthly'],
            'rows.*.notes_override' => ['nullable', 'string'],
        ];
    }
}
