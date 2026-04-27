<?php

declare(strict_types=1);

namespace App\Ports\Out\PushNotification;

use App\Application\PushNotification\DTO\PushNotificationPayload;
use App\Application\PushNotification\DTO\PushNotificationSendResult;
use App\Application\PushNotification\DTO\StoredPushSubscription;

interface PushNotificationSenderPort
{
    public function send(
        StoredPushSubscription $subscription,
        PushNotificationPayload $payload,
    ): PushNotificationSendResult;
}
