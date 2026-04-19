<?php

declare(strict_types=1);

namespace App\Adapters\Out\Reporting\Queries\OperationalProfit;

use Illuminate\Support\Facades\DB;

final class CashFlowMetricQuery
{
    public function cashIn(string $fromDate, string $toDate): int
    {
        return (int) (DB::table('customer_payments')
            ->whereBetween(DB::raw('DATE(paid_at)'), [$fromDate, $toDate])
            ->sum('amount_rupiah') ?? 0);
    }

    public function refund(string $fromDate, string $toDate): int
    {
        return (int) (DB::table('customer_refunds')
            ->whereBetween(DB::raw('DATE(refunded_at)'), [$fromDate, $toDate])
            ->sum('amount_rupiah') ?? 0);
    }
}
