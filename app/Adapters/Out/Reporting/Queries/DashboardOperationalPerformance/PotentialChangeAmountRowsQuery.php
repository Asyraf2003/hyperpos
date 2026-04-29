<?php

declare(strict_types=1);

namespace App\Adapters\Out\Reporting\Queries\DashboardOperationalPerformance;

use Illuminate\Support\Facades\DB;

final class PotentialChangeAmountRowsQuery
{
    /**
     * @return list<int>
     */
    public function rows(string $fromDate, string $toDate): array
    {
        $rows = DB::table('customer_payment_cash_details')
            ->join(
                'customer_payments',
                'customer_payments.id',
                '=',
                'customer_payment_cash_details.customer_payment_id',
            )
            ->whereBetween(DB::raw('DATE(customer_payments.paid_at)'), [$fromDate, $toDate])
            ->orderBy('customer_payments.paid_at')
            ->orderBy('customer_payment_cash_details.customer_payment_id')
            ->pluck('customer_payment_cash_details.change_rupiah')
            ->all();

        return array_map(static fn (mixed $amount): int => (int) $amount, $rows);
    }
}
