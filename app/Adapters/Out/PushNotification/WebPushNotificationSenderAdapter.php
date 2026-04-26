<?php

declare(strict_types=1);

namespace App\Adapters\Out\PushNotification;

use App\Application\PushNotification\DTO\PushNotificationPayload;
use App\Application\PushNotification\DTO\StoredPushSubscription;
use App\Ports\Out\PushNotification\PushNotificationSenderPort;
use InvalidArgumentException;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use RuntimeException;

final class WebPushNotificationSenderAdapter implements PushNotificationSenderPort
{
    public function send(StoredPushSubscription $subscription, PushNotificationPayload $payload): bool
    {
        $webPush = new WebPush([
            'VAPID' => [
                'subject' => $this->requiredConfig('services.webpush.vapid_subject'),
                'publicKey' => $this->requiredConfig('services.webpush.vapid_public_key'),
                'privateKey' => $this->requiredConfig('services.webpush.vapid_private_key'),
            ],
        ]);

        $webSubscription = Subscription::create([
            'endpoint' => $subscription->endpoint,
            'keys' => [
                'p256dh' => $subscription->publicKey,
                'auth' => $subscription->authToken,
            ],
            'contentEncoding' => $subscription->contentEncoding,
        ]);

        $encodedPayload = json_encode($payload->toArray(), JSON_THROW_ON_ERROR);

        if (! is_string($encodedPayload)) {
            throw new RuntimeException('Payload push notification gagal diencode.');
        }

        $report = $webPush->sendOneNotification($webSubscription, $encodedPayload);

        return $report->isSuccess();
    }

    private function requiredConfig(string $key): string
    {
        $value = trim((string) config($key, ''));

        if ($value === '') {
            throw new InvalidArgumentException("Konfigurasi {$key} wajib diisi.");
        }

        return $value;
    }
}
