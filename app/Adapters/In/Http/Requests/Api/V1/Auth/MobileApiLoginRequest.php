<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Requests\Api\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;

final class MobileApiLoginRequest extends FormRequest
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
            'device_name' => ['required', 'string', 'max:120'],
        ];
    }
}
