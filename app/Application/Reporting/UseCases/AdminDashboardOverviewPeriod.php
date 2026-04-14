<?php

declare(strict_types=1);

namespace App\Application\Reporting\UseCases;

use Carbon\CarbonImmutable;

final class AdminDashboardOverviewPeriod
{
    public static function build(): array
    {
        $today = CarbonImmutable::today();

        return [
            'today' => $today->toDateString(),
            'from' => $today->startOfMonth()->toDateString(),
            'to' => $today->endOfMonth()->toDateString(),
        ];
    }
}
