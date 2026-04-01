<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Requests\Note;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class UpdateTransactionWorkspaceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(
            UpdateTransactionWorkspaceInputNormalizer::normalize($this->all())
        );
    }

    public function rules(): array
    {
        return UpdateTransactionWorkspaceRules::build();
    }

    public function withValidator($validator): void
    {
        $validator->after(function (Validator $validator): void {
            UpdateTransactionWorkspaceValidator::validate($this->all(), $validator);
        });
    }
}
