<?php

declare(strict_types=1);

namespace App\Application\PushNotification\Services;

use App\Application\Note\DTO\DueNoteReminderRow;
use App\Application\PushNotification\DTO\PushNotificationPayload;

final class DueNoteReminderPushPayloadFactory
{
    /**
     * @param list<DueNoteReminderRow> $reminders
     */
    public function make(string $today, array $reminders): PushNotificationPayload
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
            url: '/admin/notes',
            tag: 'due-note-reminder-'.$today,
        );
    }
}
