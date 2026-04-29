<?php

declare(strict_types=1);

namespace App\Adapters\Out\Reporting;

use App\Adapters\Out\Reporting\Queries\DashboardOperationalPerformance\PotentialChangeAmountRowsQuery;
use App\Adapters\Out\Reporting\Queries\DashboardOperationalPerformancePeriodQuery;
use App\Ports\Out\Reporting\DashboardOperationalPerformanceReaderPort;

final class DatabaseDashboardOperationalPerformanceReaderAdapter implements DashboardOperationalPerformanceReaderPort
{
    public function __construct(
        private readonly DashboardOperationalPerformancePeriodQuery $query,
        private readonly PotentialChangeAmountRowsQuery $potentialChangeAmounts,
    ) {
    }

    public function getOperationalPerformancePeriodRows(
        string $fromDate,
        string $toDate,
    ): array {
        return $this->query->rows($fromDate, $toDate);
    }

    public function getPotentialChangeAmountsRupiah(
        string $fromDate,
        string $toDate,
    ): array {
        return $this->potentialChangeAmounts->rows($fromDate, $toDate);
    }
}
