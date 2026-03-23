<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Requests\Expense;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateExpenseCategoryRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }
}
