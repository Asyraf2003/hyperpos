<?php

declare(strict_types=1);

namespace App\Application\PushNotification\UseCases;

use App\Application\Note\DTO\DueNoteReminderRow;
use App\Application\Note\UseCases\GetDueNoteRemindersHandler;
use App\Application\PushNotification\DTO\DueNoteReminderPushSendSummary;
use App\Application\PushNotification\DTO\PushNotificationPayload;
use App\Ports\Out\PushNotification\PushNotificationSenderPort;
use App\Ports\Out\PushNotification\PushSubscriptionReaderPort;

final class SendDueNoteReminderPushHandler
{
    public function __construct(
        private readonly GetDueNoteRemindersHandler $reminders,
        private readonly PushSubscriptionReaderPort $subscriptions,
        private readonly PushNotificationSenderPort $sender,
    ) {
    }

    public function handle(
        string $today,
        int $noteLimit = 100,
        int $subscriptionLimit = 500,
    ): DueNoteReminderPushSendSummary {
        $reminders = $this->reminders->handle($today, $noteLimit);

        if ($reminders === []) {
            return new DueNoteReminderPushSendSummary(0, 0, 0, 0);
        }

        $subscriptions = $this->subscriptions->findActive($subscriptionLimit);
        $payload = $this->payload($today, $reminders);
        $sent = 0;
        $failed = 0;

        foreach ($subscriptions as $subscription) {
            if ($this->sender->send($subscription, $payload)) {
                $sent++;

                continue;
            }

            $failed++;
        }

        return new DueNoteReminderPushSendSummary(
            dueNoteCount: count($reminders),
            subscriptionCount: count($subscriptions),
            sentCount: $sent,
            failedCount: $failed,
        );
    }

    /**
     * @param list<DueNoteReminderRow> $reminders
     */
    private function payload(string $today, array $reminders): PushNotificationPayload
    {
        $count = count($reminders);
        $overdue = count(array_filter(
            $reminders,
            fn (DueNoteReminderRow $row): bool => $row->daysOverdue > 0,
        ));
        $total = array_sum(array_map(
            fn (DueNoteReminderRow $row): int => $row->outstandingRupiah,
            $reminders,
        ));

        $body = 'Ada '.$count.' nota jatuh tempo/perlu dicek. Total tagihan Rp '
            .number_format($total, 0, ',', '.').'.';

        if ($overdue > 0) {
            $body .= ' '.$overdue.' nota sudah lewat jatuh tempo.';
        }

        return new PushNotificationPayload(
            title: 'Reminder Jatuh Tempo Nota',
            body: $body,
            icon: '/assets/compiled/svg/favicon.svg',
            badge: '/assets/compiled/svg/favicon.svg',
            url: '/admin/due-note-reminders?today='.rawurlencode($today),
            tag: 'due-note-reminder-'.$today,
        );
    }
}
