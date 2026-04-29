<?php

declare(strict_types=1);

namespace Tests\Support\PushNotification;

use App\Application\PushNotification\DTO\PushNotificationPayload;
use App\Application\PushNotification\DTO\PushNotificationSendResult;
use App\Application\PushNotification\DTO\StoredPushSubscription;
use App\Ports\Out\PushNotification\PushNotificationSenderPort;

final class RecordingPushNotificationSender implements PushNotificationSenderPort
{
    /** @var list<StoredPushSubscription> */
    public array $subscriptions = [];

    /** @var list<PushNotificationPayload> */
    public array $payloads = [];

    public function send(
        StoredPushSubscription $subscription,
        PushNotificationPayload $payload,
    ): PushNotificationSendResult {
        $this->subscriptions[] = $subscription;
        $this->payloads[] = $payload;

        return PushNotificationSendResult::success(201, 'Created');
    }
}
