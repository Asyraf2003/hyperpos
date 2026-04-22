<?php

declare(strict_types=1);

namespace App\Ports\Out\Note;

interface WorkItemStoreStockLineReaderPort
{
    /**
     * @return list<string>
     */
    public function listIdsByWorkItemId(string $workItemId): array;
}
