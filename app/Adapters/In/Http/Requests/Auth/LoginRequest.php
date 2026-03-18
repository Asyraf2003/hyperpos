<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

final class LoginRequest extends FormRequest
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
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function credentials(): array
    {
        return [
            'email' => trim((string) $this->input('email')),
            'password' => (string) $this->input('password'),
        ];
    }
}