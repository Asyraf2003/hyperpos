<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Requests\Note;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class CreateNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $normalizer = new CreateNoteRowInputNormalizer();

        $this->merge([
            'customer_name' => $this->trimOrNull($this->input('customer_name')),
            'customer_phone' => $this->trimOrNull($this->input('customer_phone')),
            'transaction_date' => $this->trimOrNull($this->input('transaction_date')),
            'rows' => $normalizer->normalizeRows($this->input('rows')),
        ]);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string'],
            'customer_phone' => ['nullable', 'string'],
            'transaction_date' => ['required', 'date_format:Y-m-d'],

            'rows' => ['required', 'array', 'min:1'],
            'rows.*.line_type' => ['required', 'string', 'in:product,service'],

            'rows.*.product_id' => ['nullable', 'string'],
            'rows.*.qty' => ['nullable', 'integer', 'min:1'],

            'rows.*.service_name' => ['nullable', 'string'],
            'rows.*.service_price_rupiah' => ['nullable', 'integer', 'min:1'],
            'rows.*.service_notes' => ['nullable', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $rowValidator = new CreateNoteRowValidator();

        $validator->after(
            fn (Validator $v) => $rowValidator->validate($this->input('rows', []), $v)
        );
    }

    private function trimOrNull(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }
}
