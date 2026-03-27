<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Requests\Note;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class RecordNotePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'selected_row_ids' => ['required', 'array', 'min:1'],
            'selected_row_ids.*' => ['required', 'string'],
            'payment_method' => ['required', 'string', 'in:cash,tf'],
            'paid_at' => ['required', 'date_format:Y-m-d'],
            'amount_received' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(fn (Validator $v) => $this->validateCashFields($v));
    }

    private function validateCashFields(Validator $validator): void
    {
        if ($this->input('payment_method') !== 'cash') {
            return;
        }

        if ($this->input('amount_received') === null) {
            $validator->errors()->add('amount_received', 'Uang masuk wajib diisi untuk pembayaran cash.');
        }
    }
}
