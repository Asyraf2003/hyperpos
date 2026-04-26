<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\PushNotification;

use App\Adapters\In\Http\Requests\PushNotification\StorePushSubscriptionRequest;
use App\Application\PushNotification\DTO\PushSubscriptionData;
use App\Application\PushNotification\UseCases\StorePushSubscriptionHandler;
use Illuminate\Http\JsonResponse;

final class StorePushSubscriptionController
{
    public function __invoke(
        StorePushSubscriptionRequest $request,
        StorePushSubscriptionHandler $handler,
    ): JsonResponse {
        $data = $request->validated();
        $keys = $data['keys'];

        $handler->handle(new PushSubscriptionData(
            userId: (int) $request->user()->getAuthIdentifier(),
            endpoint: (string) $data['endpoint'],
            publicKey: (string) $keys['p256dh'],
            authToken: (string) $keys['auth'],
            contentEncoding: (string) ($data['contentEncoding'] ?? 'aes128gcm'),
            userAgent: $request->userAgent(),
        ));

        return response()->json([
            'data' => [
                'stored' => true,
            ],
        ]);
    }
}
