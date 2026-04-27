<?php

declare(strict_types=1);

namespace App\Application\PushNotification\DTO;

final readonly class PushNotificationSendResult
{
    public function __construct(
        public bool $success,
        public bool $subscriptionExpired,
        public ?int $responseStatus,
        public ?string $responseReason,
        public string $reason,
    ) {
    }

    public static function success(
        ?int $responseStatus = null,
        ?string $responseReason = null,
    ): self {
        return new self(
            success: true,
            subscriptionExpired: false,
            responseStatus: $responseStatus,
            responseReason: $responseReason,
            reason: 'OK',
        );
    }

    public static function failed(
        bool $subscriptionExpired,
        ?int $responseStatus,
        ?string $responseReason,
        string $reason,
    ): self {
        $trimmedReason = trim($reason);

        return new self(
            success: false,
            subscriptionExpired: $subscriptionExpired,
            responseStatus: $responseStatus,
            responseReason: $responseReason === null ? null : trim($responseReason),
            reason: $trimmedReason === '' ? 'Push notification failed.' : $trimmedReason,
        );
    }
}
