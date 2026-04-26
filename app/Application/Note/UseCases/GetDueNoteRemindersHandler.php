<?php

declare(strict_types=1);

namespace App\Application\Note\UseCases;

use App\Application\Note\DTO\DueNoteReminderRow;
use App\Ports\Out\Note\DueNoteReminderReaderPort;

final class GetDueNoteRemindersHandler
{
    public function __construct(
        private readonly DueNoteReminderReaderPort $reminders,
    ) {
    }

    /**
     * @return list<DueNoteReminderRow>
     */
    public function handle(string $today, int $limit = 100): array
    {
        return $this->reminders->findDueReminders($today, $limit);
    }
}
