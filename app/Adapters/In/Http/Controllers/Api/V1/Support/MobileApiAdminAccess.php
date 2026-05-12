<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Api\V1\Support;

use App\Application\IdentityAccess\Services\LoginActorAccessDecision;
use App\Application\MobileApi\Auth\DTO\MobileApiActor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class MobileApiAdminAccess
{
    public function actorOrError(Request $request, string $adminOnlyMessage): MobileApiActor|JsonResponse
    {
        $actor = $request->attributes->get('mobile_api_actor');

        if (! $actor instanceof MobileApiActor) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Autentikasi diperlukan.',
                'errors' => [
                    'token' => ['UNAUTHENTICATED'],
                ],
            ], 401);
        }

        if ($actor->role !== LoginActorAccessDecision::ADMIN) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => $adminOnlyMessage,
                'errors' => [
                    'role' => ['ADMIN_ONLY'],
                ],
            ], 403);
        }

        return $actor;
    }
}
