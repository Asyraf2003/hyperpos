<?php

declare(strict_types=1);

namespace App\Application\PushNotification\DTO;

final readonly class DueNoteReminderPushSendSummary
{
    public function __construct(
        public int $dueNoteCount,
        public int $subscriptionCount,
        public int $sentCount,
        public int $failedCount,
    ) {
    }
}
