<?php

declare(strict_types=1);

namespace App\Adapters\Out\Reporting\Queries\DashboardOperationalPerformance;

use Illuminate\Support\Facades\DB;

final class ExternalPurchaseCostPerDayQuery
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
        return DB::table('work_item_external_purchase_lines')
            ->join(
                'work_items',
                'work_items.id',
                '=',
                'work_item_external_purchase_lines.work_item_id',
            )
            ->join('notes', 'notes.id', '=', 'work_items.note_id')
            ->whereBetween('notes.transaction_date', [$fromDate, $toDate])
            ->selectRaw(
                'notes.transaction_date as period_key, ' .
                'COALESCE(SUM(work_item_external_purchase_lines.line_total_rupiah), 0) as amount_rupiah'
            )
            ->groupBy('notes.transaction_date')
            ->orderBy('notes.transaction_date')
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
