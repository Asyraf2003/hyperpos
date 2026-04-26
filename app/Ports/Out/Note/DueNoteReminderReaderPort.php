<?php

declare(strict_types=1);

namespace App\Ports\Out\Note;

use App\Application\Note\DTO\DueNoteReminderRow;

interface DueNoteReminderReaderPort
{
    /**
     * @return list<DueNoteReminderRow>
     */
    public function findDueReminders(string $today, int $limit = 100): array;
}
