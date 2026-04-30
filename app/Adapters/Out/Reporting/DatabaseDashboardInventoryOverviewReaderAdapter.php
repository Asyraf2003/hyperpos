<?php

declare(strict_types=1);

namespace App\Adapters\Out\Reporting;

use App\Adapters\Out\Reporting\Queries\DashboardInventory\DashboardInventorySummaryQuery;
use App\Adapters\Out\Reporting\Queries\DashboardInventory\DashboardRestockPriorityQuery;
use App\Ports\Out\Reporting\DashboardInventoryOverviewReaderPort;

final class DatabaseDashboardInventoryOverviewReaderAdapter implements DashboardInventoryOverviewReaderPort
{
    public function __construct(
        private readonly DashboardInventorySummaryQuery $summary,
        private readonly DashboardRestockPriorityQuery $restockPriority,
    ) {
    }

    public function getInventorySummary(string $fromMutationDate, string $toMutationDate): array
    {
        return $this->summary->get($fromMutationDate, $toMutationDate);
    }

    public function getRestockPriorityRows(int $limit): array
    {
        return $this->restockPriority->get($limit);
    }
}
