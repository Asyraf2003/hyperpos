<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Requests\Note;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class StoreTransactionWorkspaceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(
            StoreTransactionWorkspaceInputNormalizer::normalize($this->all())
        );
    }

    public function rules(): array
    {
        return StoreTransactionWorkspaceRules::build();
    }

    public function withValidator($validator): void
    {
        $validator->after(function (Validator $validator): void {
            StoreTransactionWorkspaceValidator::validate($this->all(), $validator);
        });
    }
}
