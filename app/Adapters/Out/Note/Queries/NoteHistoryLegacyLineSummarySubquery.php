<?php

declare(strict_types=1);

namespace App\Adapters\Out\Note\Queries;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

final class NoteHistoryLegacyLineSummarySubquery
{
    public function __construct(
        private readonly NoteHistoryAggregationSubqueries $aggregations,
    ) {
    }

    public function build(): Builder
    {
        return DB::query()
            ->fromSub($this->refundProjection(), 'legacy_refund_projection')
            ->selectRaw("note_id, COALESCE(SUM(CASE WHEN refunded_rupiah > 0 THEN 1 ELSE 0 END), 0) as line_refund_count, COALESCE(SUM(CASE WHEN refunded_rupiah <= 0 AND GREATEST(subtotal_rupiah - GREATEST(allocated_rupiah - refunded_rupiah, 0), 0) > 0 THEN 1 ELSE 0 END), 0) as line_open_count, COALESCE(SUM(CASE WHEN refunded_rupiah <= 0 AND GREATEST(subtotal_rupiah - GREATEST(allocated_rupiah - refunded_rupiah, 0), 0) <= 0 THEN 1 ELSE 0 END), 0) as line_close_count")
            ->groupBy('note_id');
    }

    private function projectionBase(): Builder
    {
        $legacyAllocationTotals = $this->aggregations->legacyAllocationTotals();
        $legacyRefundTotals = $this->aggregations->legacyRefundTotals();

        return DB::table('work_items')
            ->leftJoinSub($legacyAllocationTotals, 'legacy_allocation_totals', fn ($join) => $join->on('legacy_allocation_totals.note_id', '=', 'work_items.note_id'))
            ->leftJoinSub($legacyRefundTotals, 'legacy_refund_totals', fn ($join) => $join->on('legacy_refund_totals.note_id', '=', 'work_items.note_id'))
            ->selectRaw("work_items.note_id, work_items.id as work_item_id, work_items.line_no, work_items.subtotal_rupiah, COALESCE(legacy_allocation_totals.allocated_rupiah, 0) as note_allocated_rupiah, COALESCE(legacy_refund_totals.refunded_rupiah, 0) as note_refunded_rupiah, SUM(work_items.subtotal_rupiah) OVER (PARTITION BY work_items.note_id ORDER BY work_items.line_no, work_items.id ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW) as running_subtotal_rupiah");
    }

    private function allocatedProjection(): Builder
    {
        return DB::query()
            ->fromSub($this->projectionBase(), 'legacy_projection_base')
            ->selectRaw("note_id, work_item_id, line_no, subtotal_rupiah, note_refunded_rupiah, GREATEST(LEAST(note_allocated_rupiah - (running_subtotal_rupiah - subtotal_rupiah), subtotal_rupiah), 0) as allocated_rupiah");
    }

    private function refundProjection(): Builder
    {
        return DB::query()
            ->fromSub($this->allocatedProjection(), 'legacy_allocated_projection')
            ->selectRaw("note_id, work_item_id, line_no, subtotal_rupiah, allocated_rupiah, GREATEST(LEAST(note_refunded_rupiah - (SUM(allocated_rupiah) OVER (PARTITION BY note_id ORDER BY line_no, work_item_id ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW) - allocated_rupiah), allocated_rupiah), 0) as refunded_rupiah");
    }
}
