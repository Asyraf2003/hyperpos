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
        $this->merge([
            'payment_scope' => $this->normalizeString($this->input('payment_scope')),
            'payment_method' => $this->normalizeString($this->input('payment_method')),
            'paid_at' => $this->normalizeString($this->input('paid_at')),
            'amount_paid' => $this->normalizeInteger($this->input('amount_paid')),
            'amount_received' => $this->normalizeInteger($this->input('amount_received')),
        ]);
    }

    public function rules(): array
    {
        return [
            'payment_scope' => ['required', 'string', 'in:full,partial'],
            'payment_method' => ['required', 'string', 'in:cash,tf'],
            'paid_at' => ['required', 'date_format:Y-m-d'],
            'amount_paid' => ['nullable', 'integer', 'min:1'],
            'amount_received' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(fn (Validator $v) => $this->validateBusinessRules($v));
    }

    private function validateBusinessRules(Validator $validator): void
    {
        if ($this->input('payment_scope') === 'partial' && $this->input('amount_paid') === null) {
            $validator->errors()->add('amount_paid', 'Nominal pembayaran wajib diisi untuk pembayaran sebagian.');
        }

        if ($this->input('payment_method') === 'cash' && $this->input('amount_received') === null) {
            $validator->errors()->add('amount_received', 'Uang masuk wajib diisi untuk pembayaran cash.');
        }
    }

    private function normalizeString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function normalizeInteger(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value;
        }

        if (! is_string($value)) {
            return null;
        }

        $cleaned = preg_replace('/[^0-9]/', '', $value);

        if (! is_string($cleaned) || $cleaned === '') {
            return null;
        }

        return (int) $cleaned;
    }
}
