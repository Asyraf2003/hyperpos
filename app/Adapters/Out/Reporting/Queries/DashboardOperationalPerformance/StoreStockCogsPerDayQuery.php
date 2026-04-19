<?php

declare(strict_types=1);

namespace App\Adapters\Out\Reporting\Queries\DashboardOperationalPerformance;

use Illuminate\Support\Facades\DB;

final class StoreStockCogsPerDayQuery
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
        return DB::table('inventory_movements')
            ->where('movement_type', 'stock_out')
            ->where('source_type', 'work_item_store_stock_line')
            ->whereBetween('tanggal_mutasi', [$fromDate, $toDate])
            ->selectRaw(
                'tanggal_mutasi as period_key, ' .
                'COALESCE(SUM(ABS(total_cost_rupiah)), 0) as amount_rupiah'
            )
            ->groupBy('tanggal_mutasi')
            ->orderBy('tanggal_mutasi')
            ->get()
            ->map(static fn (object $row): array => [
                'period_key' => (string) $row->period_key,
                'period_label' => (string) $row->period_key,
                'amount_rupiah' => (int) $row->amount_rupiah,
            ])
            ->values()
            ->all();
    }
}
