<?php

declare(strict_types=1);

namespace App\Application\PushNotification\UseCases;

use App\Ports\Out\PushNotification\PushSubscriptionWriterPort;

final class DeletePushSubscriptionHandler
{
    public function __construct(
        private readonly PushSubscriptionWriterPort $subscriptions,
    ) {
    }

    public function handle(int $userId, string $endpoint): void
    {
        $this->subscriptions->deleteForUserEndpoint($userId, $endpoint);
    }
}
