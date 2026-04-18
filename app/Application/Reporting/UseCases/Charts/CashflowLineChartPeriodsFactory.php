<?php

declare(strict_types=1);

namespace App\Application\Reporting\UseCases\Charts;

use Carbon\CarbonImmutable;

final class CashflowLineChartPeriodsFactory
{
    /**
     * @return array<string, array{cash_in:int, cash_out:int}>
     */
    public function create(string $fromDate, string $toDate): array
    {
        $cursor = CarbonImmutable::parse($fromDate);
        $end = CarbonImmutable::parse($toDate);
        $periods = [];

        while ($cursor->lte($end)) {
            $date = $cursor->toDateString();
            $periods[$date] = [
                'cash_in' => 0,
                'cash_out' => 0,
            ];

            $cursor = $cursor->addDay();
        }

        return $periods;
    }
}
