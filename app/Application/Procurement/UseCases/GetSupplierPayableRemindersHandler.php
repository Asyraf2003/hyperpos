<?php

declare(strict_types=1);

namespace App\Application\Procurement\UseCases;

use App\Application\Procurement\DTO\SupplierPayableReminderRow;
use App\Ports\Out\Procurement\SupplierPayableReminderReaderPort;

final class GetSupplierPayableRemindersHandler
{
    public function __construct(
        private readonly SupplierPayableReminderReaderPort $reminders,
    ) {
    }

    /**
     * @return list<SupplierPayableReminderRow>
     */
    public function handle(string $today, int $limit = 100): array
    {
        return $this->reminders->findDueReminders($today, $limit);
    }
}
