<?php

declare(strict_types=1);

namespace App\Application\Reporting\UseCases;

use Carbon\CarbonImmutable;

final class AdminDashboardPeriod
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
        $today = CarbonImmutable::today();
        $anchor = self::anchorMonth($month, $today);
        $to = $anchor->isSameMonth($today)
            ? $today
            : $anchor->endOfMonth();

        return [
            'today' => $today->toDateString(),
            'active_month' => $anchor->format('Y-m'),
            'from' => $anchor->startOfMonth()->toDateString(),
            'to' => $to->toDateString(),
        ];
    }

    private static function anchorMonth(?string $month, CarbonImmutable $today): CarbonImmutable
    {
        if ($month === null || preg_match('/^\d{4}-\d{2}$/', $month) !== 1) {
            return $today->startOfMonth();
        }

        return CarbonImmutable::createFromFormat('Y-m-d', $month . '-01')
            ->startOfMonth();
    }
}
