<?php

declare(strict_types=1);

namespace App\Ports\Out\PushNotification;

use App\Application\PushNotification\DTO\PushSubscriptionData;

interface PushSubscriptionWriterPort
{
    public function upsert(PushSubscriptionData $subscription): void;

    public function deleteForUserEndpoint(int $userId, string $endpoint): void;

    public function markExpiredByEndpoint(
        string $endpoint,
        ?int $failureStatus,
        ?string $failureReason,
    ): void;
}
