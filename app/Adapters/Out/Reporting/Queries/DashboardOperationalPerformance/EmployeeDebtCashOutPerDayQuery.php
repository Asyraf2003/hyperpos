<?php

declare(strict_types=1);

namespace App\Adapters\Out\Reporting\Queries\DashboardOperationalPerformance;

use Illuminate\Support\Facades\DB;

final class EmployeeDebtCashOutPerDayQuery
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
        return DB::table('employee_debts')
            ->whereBetween('employee_debts.created_at', [
                $this->startOfDay($fromDate),
                $this->endOfDay($toDate),
            ])
            ->selectRaw(
                'DATE(employee_debts.created_at) as period_key, ' .
                'COALESCE(SUM(employee_debts.total_debt), 0) as amount_rupiah'
            )
            ->groupBy(DB::raw('DATE(employee_debts.created_at)'))
            ->orderBy(DB::raw('DATE(employee_debts.created_at)'))
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
