<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Api\V1\Auth;

use App\Adapters\In\Http\Requests\Api\V1\Auth\MobileApiLoginRequest;
use App\Application\MobileApi\Auth\UseCases\LoginMobileApiUserHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class LoginMobileApiController extends Controller
{
    public function __construct(private readonly LoginMobileApiUserHandler $login)
    {
    }

    public function __invoke(MobileApiLoginRequest $request): JsonResponse
    {
        $result = $this->login->handle(
            email: (string) $request->input('email'),
            password: (string) $request->input('password'),
            deviceName: (string) $request->input('device_name'),
        );

        if (!$result->success) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => $result->message,
                'errors' => $result->errors,
            ], $result->status);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $result->token?->plainToken,
                'token_type' => $result->token?->tokenType,
                'expires_at' => $result->token?->expiresAt->toIso8601String(),
                'actor' => $result->actor?->toArray(),
            ],
            'errors' => null,
        ]);
    }
}
