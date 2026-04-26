<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Requests\PushNotification;

use Illuminate\Foundation\Http\FormRequest;

final class DeletePushSubscriptionRequest extends FormRequest
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
            'endpoint' => ['required', 'string', 'url', 'max:4096'],
        ];
    }
}
