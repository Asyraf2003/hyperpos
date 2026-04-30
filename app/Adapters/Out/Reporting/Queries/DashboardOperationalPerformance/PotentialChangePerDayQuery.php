<?php

declare(strict_types=1);

namespace App\Adapters\Out\Reporting\Queries\DashboardOperationalPerformance;

use Illuminate\Support\Facades\DB;

final class PotentialChangePerDayQuery
{
    /**
     * @return list<array{
     *   period_key:string,
     *   period_label:string,
     *   amount_rupiah:int
     * }>
     */
    public function rows(string $fromDate, string $toDate): array
    {
        return DB::table('customer_payment_cash_details')
            ->join(
                'customer_payments',
                'customer_payments.id',
                '=',
                'customer_payment_cash_details.customer_payment_id',
            )
            ->whereBetween('customer_payments.paid_at', [
                $this->startOfDay($fromDate),
                $this->endOfDay($toDate),
            ])
            ->selectRaw(
                'DATE(customer_payments.paid_at) as period_key, '
                .'COALESCE(SUM(customer_payment_cash_details.change_rupiah), 0) as amount_rupiah'
            )
            ->groupBy(DB::raw('DATE(customer_payments.paid_at)'))
            ->orderBy(DB::raw('DATE(customer_payments.paid_at)'))
            ->get()
            ->map(static fn (object $row): array => [
                'period_key' => (string) $row->period_key,
                'period_label' => (string) $row->period_key,
                'amount_rupiah' => (int) $row->amount_rupiah,
            ])
            ->values()
            ->all();
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
