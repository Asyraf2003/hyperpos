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
        $rows = $this->input('rows');

        if (! is_array($rows)) {
            $rows = [];
        }

        $normalizedRows = array_map(
            function ($row): array {
                if (! is_array($row)) {
                    $row = [];
                }

                return [
                    'line_type' => $this->trimOrNull($row['line_type'] ?? null),
                    'product_id' => $this->trimOrNull($row['product_id'] ?? null),
                    'qty' => $this->toNullableInt($row['qty'] ?? null),
                    'service_name' => $this->trimOrNull($row['service_name'] ?? null),
                    'service_price_rupiah' => $this->toNullableInt($row['service_price_rupiah'] ?? null),
                    'service_notes' => $this->trimOrNull($row['service_notes'] ?? null),
                ];
            },
            $rows
        );

        $this->merge([
            'customer_name' => $this->trimOrNull($this->input('customer_name')),
            'customer_phone' => $this->trimOrNull($this->input('customer_phone')),
            'transaction_date' => $this->trimOrNull($this->input('transaction_date')),
            'rows' => $normalizedRows,
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
        $validator->after(fn (Validator $v) => $this->validateRows($v));
    }

    private function validateRows(Validator $validator): void
    {
        $rows = $this->input('rows', []);

        if (! is_array($rows)) {
            return;
        }

        foreach ($rows as $index => $row) {
            if (! is_array($row)) {
                continue;
            }

            $lineType = $row['line_type'] ?? null;

            if ($lineType === 'product') {
                if (($row['product_id'] ?? null) === null) {
                    $validator->errors()->add(
                        "rows.$index.product_id",
                        'Produk wajib dipilih untuk baris bertipe Produk.'
                    );
                }

                if (($row['qty'] ?? null) === null) {
                    $validator->errors()->add(
                        "rows.$index.qty",
                        'Qty wajib diisi untuk baris bertipe Produk.'
                    );
                }
            }

            if ($lineType === 'service') {
                if (($row['service_name'] ?? null) === null) {
                    $validator->errors()->add(
                        "rows.$index.service_name",
                        'Nama servis wajib diisi untuk baris bertipe Servis.'
                    );
                }

                if (($row['service_price_rupiah'] ?? null) === null) {
                    $validator->errors()->add(
                        "rows.$index.service_price_rupiah",
                        'Harga servis wajib diisi untuk baris bertipe Servis.'
                    );
                }
            }
        }
    }

    private function trimOrNull(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function toNullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_int($value)) {
            return $value;
        }

        if (is_string($value)) {
            $trimmed = trim($value);

            if ($trimmed === '' || ! preg_match('/^-?\d+$/', $trimmed)) {
                return null;
            }

            return (int) $trimmed;
        }

        return null;
    }
}
