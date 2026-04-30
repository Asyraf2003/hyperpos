<?php

declare(strict_types=1);

namespace App\Application\PushNotification\DTO;

final readonly class SupplierPayableReminderPushSendSummary
{
    public function __construct(
        public int $supplierPayableReminderCount,
        public int $subscriptionCount,
        public int $sentCount,
        public int $failedCount,
        public int $expiredCount,
    ) {
    }
}
