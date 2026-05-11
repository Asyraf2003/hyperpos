<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Api\V1\Auth;

use App\Application\MobileApi\Auth\DTO\MobileApiActor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class MeMobileApiController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $actor = $request->attributes->get('mobile_api_actor');

        if (!$actor instanceof MobileApiActor) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Autentikasi diperlukan.',
                'errors' => [
                    'token' => ['UNAUTHENTICATED'],
                ],
            ], 401);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'actor' => $actor->toArray(),
            ],
            'errors' => null,
        ]);
    }
}
