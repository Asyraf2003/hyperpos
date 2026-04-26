<?php

declare(strict_types=1);

namespace App\Application\PushNotification\DTO;

final readonly class PushSubscriptionData
{
    public function __construct(
        public int $userId,
        public string $endpoint,
        public string $publicKey,
        public string $authToken,
        public string $contentEncoding,
        public ?string $userAgent,
    ) {
    }
}
