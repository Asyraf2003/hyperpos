<?php

declare(strict_types=1);

namespace App\Application\Reporting\UseCases;

use Carbon\CarbonImmutable;

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
    public static function build(): array
    {
        $today = CarbonImmutable::today();

        return [
            'today' => $today->toDateString(),
            'active_month' => $today->format('Y-m'),
            'from' => $today->startOfMonth()->toDateString(),
            'to' => $today->toDateString(),
        ];
    }
}
