<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Requests\EmployeeFinance;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateEmployeeProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $employeeName = $this->trimOrNull('employee_name') ?? $this->trimOrNull('name');
        $salaryBasisType = $this->trimOrNull('salary_basis_type') ?? $this->trimOrNull('pay_period_value');
        $defaultSalaryAmount = $this->integerOrNull('default_salary_amount') ?? $this->integerOrNull('base_salary_amount');

        $this->merge([
            'employee_name' => $employeeName,
            'name' => $employeeName,
            'phone' => $this->trimOrNull('phone'),
            'salary_basis_type' => $salaryBasisType,
            'pay_period_value' => $salaryBasisType,
            'default_salary_amount' => $defaultSalaryAmount,
            'base_salary_amount' => $defaultSalaryAmount,
            'change_reason' => $this->trimOrNull('change_reason'),
            'started_at' => $this->trimOrNull('started_at'),
            'ended_at' => $this->trimOrNull('ended_at'),
        ]);
    }

    public function rules(): array
    {
        return [
            'employee_name' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'salary_basis_type' => ['required', 'string', 'in:daily,weekly,monthly,manual'],
            'pay_period_value' => ['required', 'string', 'in:daily,weekly,monthly,manual'],
            'default_salary_amount' => ['present', 'nullable', 'integer', 'min:1'],
            'base_salary_amount' => ['present', 'nullable', 'integer', 'min:1'],
            'change_reason' => ['present', 'nullable', 'string'],
            'started_at' => ['nullable', 'date'],
            'ended_at' => ['nullable', 'date', 'after_or_equal:started_at'],
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

    private function integerOrNull(string $key): ?int
    {
        $value = $this->input($key);

        if ($value === null) {
            return null;
        }

        if (is_string($value) && trim($value) === '') {
            return null;
        }

        if (! is_numeric($value)) {
            return null;
        }

        return (int) $value;
    }
}
