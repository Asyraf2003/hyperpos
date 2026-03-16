<?php
declare(strict_types=1);
namespace App\Adapters\In\Http\Requests\EmployeeFinance;
use Illuminate\Foundation\Http\FormRequest;

class RegisterEmployeeRequest extends FormRequest {
    public function authorize(): bool { return true; }
    public function rules(): array {
        return [
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'base_salary_amount' => 'required|integer|min:0',
            'pay_period_value' => 'required|string|in:weekly,monthly',
        ];
    }
}
