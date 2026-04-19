<?php

declare(strict_types=1);

namespace App\Adapters\Out\Reporting;

use App\Adapters\Out\Reporting\Queries\DashboardTopSellingProductQuery;
use App\Ports\Out\Reporting\DashboardTopSellingProductReaderPort;

final class DatabaseDashboardTopSellingProductReaderAdapter implements DashboardTopSellingProductReaderPort
{
    public function __construct(
        private readonly DashboardTopSellingProductQuery $query,
    ) {
    }

    public function getTopSellingProducts(
        string $fromTransactionDate,
        string $toTransactionDate,
        int $limit,
    ): array {
        return $this->query->rows($fromTransactionDate, $toTransactionDate, $limit);
    }
}
