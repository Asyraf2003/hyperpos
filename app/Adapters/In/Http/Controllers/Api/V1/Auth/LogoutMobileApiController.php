<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Api\V1\Auth;

use App\Application\MobileApi\Auth\UseCases\LogoutMobileApiTokenHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class LogoutMobileApiController extends Controller
{
    public function __construct(private readonly LogoutMobileApiTokenHandler $logout)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $tokenId = $request->attributes->get('mobile_api_token_id');

        if (!is_string($tokenId) || $tokenId === '') {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Autentikasi diperlukan.',
                'errors' => [
                    'token' => ['UNAUTHENTICATED'],
                ],
            ], 401);
        }

        $this->logout->handle($tokenId);

        return response()->json([
            'success' => true,
            'data' => null,
            'message' => 'Logout berhasil.',
            'errors' => null,
        ]);
    }
}
