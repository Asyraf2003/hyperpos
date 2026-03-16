<?php
declare(strict_types=1);
namespace App\Adapters\In\Http\Requests\EmployeeFinance;
use Illuminate\Foundation\Http\FormRequest;

class PayEmployeeDebtRequest extends FormRequest {
    public function authorize(): bool { return true; }
    public function rules(): array {
        return [
            'payment_amount' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ];
    }
}
