<?php

declare(strict_types=1);

namespace App\Adapters\Out\Reporting\Queries\DashboardInventory;

final class DashboardInventorySummaryQuery
{
    public function __construct(
        private readonly DashboardInventorySnapshotSummaryQuery $snapshot,
        private readonly DashboardInventoryMovementSummaryQuery $movement,
    ) {
    }

    /**
     * @return array<string, int>
     */
    public function get(string $fromMutationDate, string $toMutationDate): array
    {
        return array_merge(
            $this->snapshot->get(),
            $this->movement->get($fromMutationDate, $toMutationDate),
        );
    }
}
