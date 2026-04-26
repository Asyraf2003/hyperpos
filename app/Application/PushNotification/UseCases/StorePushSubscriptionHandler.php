<?php

declare(strict_types=1);

namespace App\Application\PushNotification\UseCases;

use App\Application\PushNotification\DTO\PushSubscriptionData;
use App\Ports\Out\PushNotification\PushSubscriptionWriterPort;

final class StorePushSubscriptionHandler
{
    public function __construct(
        private readonly PushSubscriptionWriterPort $subscriptions,
    ) {
    }

    public function handle(PushSubscriptionData $subscription): void
    {
        $this->subscriptions->upsert($subscription);
    }
}
