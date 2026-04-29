<?php

declare(strict_types=1);

namespace Tests\Support\PushNotification;

use App\Application\PushNotification\DTO\PushNotificationPayload;
use App\Application\PushNotification\DTO\PushNotificationSendResult;
use App\Application\PushNotification\DTO\StoredPushSubscription;
use App\Ports\Out\PushNotification\PushNotificationSenderPort;

final class ExpiringPushNotificationSender implements PushNotificationSenderPort
{
    /** @var list<StoredPushSubscription> */
    public array $subscriptions = [];

    /** @var list<PushNotificationPayload> */
    public array $payloads = [];

    public function __construct(
        private readonly string $expiredEndpointNeedle,
    ) {
    }

    public function send(
        StoredPushSubscription $subscription,
        PushNotificationPayload $payload,
    ): PushNotificationSendResult {
        $this->subscriptions[] = $subscription;
        $this->payloads[] = $payload;

        if (str_contains($subscription->endpoint, $this->expiredEndpointNeedle)) {
            return PushNotificationSendResult::failed(
                subscriptionExpired: true,
                responseStatus: 410,
                responseReason: 'Gone',
                reason: 'push subscription has unsubscribed or expired.',
            );
        }

        return PushNotificationSendResult::success(201, 'Created');
    }
}
