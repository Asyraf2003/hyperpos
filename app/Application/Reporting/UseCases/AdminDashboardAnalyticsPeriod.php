<?php

declare(strict_types=1);

namespace App\Application\Reporting\UseCases;

final class AdminDashboardAnalyticsPeriod
{
    /**
     * @return array{
     *   today:string,
     *   active_month:string,
     *   from:string,
     *   to:string
     * }
     */
    public static function build(?string $month = null): array
    {
        return AdminDashboardPeriod::build($month);
    }
}
