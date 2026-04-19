<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Requests\Expense;

use Illuminate\Foundation\Http\FormRequest;

final class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'string'],
            'amount_rupiah' => ['required', 'integer', 'min:1'],
            'expense_date' => ['required', 'date_format:Y-m-d'],
            'description' => ['required', 'string', 'max:255'],
            'payment_method' => ['required', 'in:cash,tf'],
        ];
    }
}
