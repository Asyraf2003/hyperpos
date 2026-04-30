<?php

declare(strict_types=1);

namespace App\Adapters\Out\Reporting\Queries\OperationalProfit;

use Illuminate\Support\Facades\DB;

final class CashFlowMetricQuery
{
    public function cashIn(string $fromDate, string $toDate): int
    {
        return (int) (DB::table('customer_payments')
            ->whereBetween('paid_at', [
                $this->startOfDay($fromDate),
                $this->endOfDay($toDate),
            ])
            ->sum('amount_rupiah') ?? 0);
    }

    public function refund(string $fromDate, string $toDate): int
    {
        return (int) (DB::table('customer_refunds')
            ->whereBetween('refunded_at', [
                $this->startOfDay($fromDate),
                $this->endOfDay($toDate),
            ])
            ->sum('amount_rupiah') ?? 0);
    }

    private function startOfDay(string $date): string
    {
        return $date . ' 00:00:00';
    }

    private function endOfDay(string $date): string
    {
        return $date . ' 23:59:59';
    }
}
