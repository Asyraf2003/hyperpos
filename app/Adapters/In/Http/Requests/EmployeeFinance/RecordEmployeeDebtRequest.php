<?php
declare(strict_types=1);
namespace App\Adapters\In\Http\Requests\EmployeeFinance;
use Illuminate\Foundation\Http\FormRequest;

class RecordEmployeeDebtRequest extends FormRequest {
    public function authorize(): bool { return true; }
    public function rules(): array {
        return [
            'employee_id' => 'required|uuid',
            'debt_amount' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ];
    }
}
