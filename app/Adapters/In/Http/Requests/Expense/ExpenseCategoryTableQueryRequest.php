<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Requests\Expense;

use Illuminate\Foundation\Http\FormRequest;

final class ExpenseCategoryTableQueryRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'q' => $this->trimOrNull('q'),
            'is_active' => $this->trimOrNull('is_active'),
            'sort_by' => $this->trimOrNull('sort_by'),
            'sort_dir' => $this->trimOrNull('sort_dir'),
        ]);
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string'],
            'is_active' => ['nullable', 'in:1,0'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'in:10'],
            'sort_by' => ['nullable', 'in:code,name,is_active'],
            'sort_dir' => ['nullable', 'in:asc,desc'],
        ];
    }

    private function trimOrNull(string $key): ?string
    {
        $value = $this->input($key);
        if (! is_string($value)) return null;
        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }
}
