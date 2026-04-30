<?php

declare(strict_types=1);

namespace App\Ports\Out\Procurement;

use App\Application\Procurement\DTO\SupplierPayableReminderRow;

interface SupplierPayableReminderReaderPort
{
    /**
     * @return list<SupplierPayableReminderRow>
     */
    public function findDueReminders(string $today, int $limit = 100): array;
}
