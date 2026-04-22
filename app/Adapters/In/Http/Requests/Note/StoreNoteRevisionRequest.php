<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Requests\Note;

use Illuminate\Foundation\Http\FormRequest;

final class StoreNoteRevisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'note.customer_name' => ['required', 'string', 'max:255'],
            'note.customer_phone' => ['nullable', 'string', 'max:255'],
            'note.transaction_date' => ['required', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.line_type' => ['required', 'string'],
            'items.*.service_name' => ['nullable', 'string'],
            'items.*.service_price' => ['nullable'],
            'items.*.product_id' => ['nullable', 'string'],
            'items.*.qty' => ['nullable'],
            'items.*.price' => ['nullable'],
            'items.*.note' => ['nullable', 'string'],
            'reason' => ['required', 'string', 'min:3'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'note.customer_name.required' => 'Nama customer wajib diisi.',
            'note.transaction_date.required' => 'Tanggal nota wajib diisi.',
            'items.required' => 'Minimal satu item wajib diisi.',
            'reason.required' => 'Alasan revisi wajib diisi.',
        ];
    }
}
