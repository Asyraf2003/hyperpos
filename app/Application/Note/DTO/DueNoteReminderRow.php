<?php

declare(strict_types=1);

namespace App\Application\Note\DTO;

final readonly class DueNoteReminderRow
{
    public function __construct(
        public string $noteId,
        public string $customerName,
        public ?string $customerPhone,
        public string $transactionDate,
        public string $dueDate,
        public int $outstandingRupiah,
        public int $daysOverdue,
    ) {
    }
}
