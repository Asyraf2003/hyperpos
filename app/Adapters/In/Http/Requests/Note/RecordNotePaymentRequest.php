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

    protected function prepareForValidation(): void
    {
        $this->merge(
            RecordNotePaymentInputNormalizer::normalize($this->all())
        );
    }

    public function rules(): array
    {
        return [
            'selected_row_ids' => ['nullable', 'array'],
            'selected_row_ids.*' => ['string'],
            'payment_scope' => ['nullable', 'string', 'in:full,partial'],
            'payment_method' => ['required', 'string', 'in:cash,tf,transfer'],
            'paid_at' => ['required', 'date_format:Y-m-d'],
            'amount_paid' => ['nullable', 'integer', 'min:1'],
            'amount_received' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function (Validator $validator): void {
            RecordNotePaymentBusinessValidator::validate($this->all(), $validator);
        });
    }
}
