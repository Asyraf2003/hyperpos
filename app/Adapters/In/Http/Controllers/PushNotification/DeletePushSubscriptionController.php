<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\PushNotification;

use App\Adapters\In\Http\Requests\PushNotification\DeletePushSubscriptionRequest;
use App\Application\PushNotification\UseCases\DeletePushSubscriptionHandler;
use Illuminate\Http\JsonResponse;

final class DeletePushSubscriptionController
{
    public function __invoke(
        DeletePushSubscriptionRequest $request,
        DeletePushSubscriptionHandler $handler,
    ): JsonResponse {
        $data = $request->validated();

        $handler->handle(
            (int) $request->user()->getAuthIdentifier(),
            (string) $data['endpoint'],
        );

        return response()->json([
            'data' => [
                'deleted' => true,
            ],
        ]);
    }
}
