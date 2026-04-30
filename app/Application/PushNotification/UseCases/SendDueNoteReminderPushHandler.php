<?php

declare(strict_types=1);

namespace App\Application\PushNotification\UseCases;

use App\Application\Note\UseCases\GetDueNoteRemindersHandler;
use App\Application\PushNotification\DTO\DueNoteReminderPushSendSummary;
use App\Application\PushNotification\Services\DueNoteReminderPushPayloadFactory;
use App\Ports\Out\PushNotification\PushNotificationSenderPort;
use App\Ports\Out\PushNotification\PushSubscriptionReaderPort;
use App\Ports\Out\PushNotification\PushSubscriptionWriterPort;

final class SendDueNoteReminderPushHandler
{
    public function __construct(
        private readonly GetDueNoteRemindersHandler $reminders,
        private readonly PushSubscriptionReaderPort $subscriptions,
        private readonly PushSubscriptionWriterPort $subscriptionWriter,
        private readonly PushNotificationSenderPort $sender,
        private readonly DueNoteReminderPushPayloadFactory $payloads,
    ) {
    }

    public function handle(
        string $today,
        int $noteLimit = 100,
        int $subscriptionLimit = 500,
    ): DueNoteReminderPushSendSummary {
        $reminders = $this->reminders->handle($today, $noteLimit);

        if ($reminders === []) {
            return new DueNoteReminderPushSendSummary(0, 0, 0, 0, 0);
        }

        $subscriptions = $this->subscriptions->findActive($subscriptionLimit);
        $payload = $this->payloads->make($today, $reminders);
        $sent = 0;
        $failed = 0;
        $expired = 0;

        foreach ($subscriptions as $subscription) {
            $result = $this->sender->send($subscription, $payload);

            if ($result->success) {
                $sent++;

                continue;
            }

            if ($result->subscriptionExpired) {
                $expired++;
                $this->subscriptionWriter->markExpiredByEndpoint(
                    $subscription->endpoint,
                    $result->responseStatus,
                    $result->reason,
                );

                continue;
            }

            $failed++;
        }

        return new DueNoteReminderPushSendSummary(
            dueNoteCount: count($reminders),
            subscriptionCount: count($subscriptions),
            sentCount: $sent,
            failedCount: $failed,
            expiredCount: $expired,
        );
    }
}
