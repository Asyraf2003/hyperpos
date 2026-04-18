<?php

declare(strict_types=1);

namespace App\Adapters\Out\Note\Queries;

use Illuminate\Support\Facades\DB;

final class CashierNoteHistoryBaseQuery
{
    /**
     * @return array<int, object>
     */
    public function fetch(CashierNoteHistoryCriteria $criteria): array
    {
        $componentAllocationTotals = DB::table('payment_component_allocations')
            ->selectRaw('note_id, COALESCE(SUM(allocated_amount_rupiah), 0) as allocated_rupiah')
            ->groupBy('note_id');

        $legacyAllocationTotals = DB::table('payment_allocations')
            ->selectRaw('note_id, COALESCE(SUM(amount_rupiah), 0) as allocated_rupiah')
            ->groupBy('note_id');

        $componentRefundTotals = DB::table('refund_component_allocations')
            ->selectRaw('note_id, COALESCE(SUM(refunded_amount_rupiah), 0) as refunded_rupiah')
            ->groupBy('note_id');

        $legacyRefundTotals = DB::table('customer_refunds')
            ->selectRaw('note_id, COALESCE(SUM(amount_rupiah), 0) as refunded_rupiah')
            ->groupBy('note_id');

        $workSummary = DB::table('work_items')
            ->selectRaw("
                note_id,
                COALESCE(SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END), 0) as open_count,
                COALESCE(SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END), 0) as done_count,
                COALESCE(SUM(CASE WHEN status = 'canceled' THEN 1 ELSE 0 END), 0) as canceled_count
            ")
            ->groupBy('note_id');

        $componentPaymentByWorkItem = DB::table('payment_component_allocations')
            ->selectRaw('note_id, work_item_id, COALESCE(SUM(allocated_amount_rupiah), 0) as allocated_rupiah')
            ->groupBy('note_id', 'work_item_id');

        $componentRefundByWorkItem = DB::table('refund_component_allocations')
            ->selectRaw('note_id, work_item_id, COALESCE(SUM(refunded_amount_rupiah), 0) as refunded_rupiah')
            ->groupBy('note_id', 'work_item_id');

        $componentLineSummary = DB::table('work_items')
            ->leftJoinSub($componentPaymentByWorkItem, 'payment_by_work_item', function ($join): void {
                $join->on('payment_by_work_item.note_id', '=', 'work_items.note_id')
                    ->on('payment_by_work_item.work_item_id', '=', 'work_items.id');
            })
            ->leftJoinSub($componentRefundByWorkItem, 'refund_by_work_item', function ($join): void {
                $join->on('refund_by_work_item.note_id', '=', 'work_items.note_id')
                    ->on('refund_by_work_item.work_item_id', '=', 'work_items.id');
            })
            ->selectRaw("
                work_items.note_id,
                COALESCE(SUM(
                    CASE
                        WHEN COALESCE(refund_by_work_item.refunded_rupiah, 0) > 0 THEN 1
                        ELSE 0
                    END
                ), 0) as line_refund_count,
                COALESCE(SUM(
                    CASE
                        WHEN COALESCE(refund_by_work_item.refunded_rupiah, 0) <= 0
                            AND GREATEST(
                                work_items.subtotal_rupiah - GREATEST(
                                    COALESCE(payment_by_work_item.allocated_rupiah, 0)
                                    - COALESCE(refund_by_work_item.refunded_rupiah, 0),
                                    0
                                ),
                                0
                            ) > 0
                        THEN 1
                        ELSE 0
                    END
                ), 0) as line_open_count,
                COALESCE(SUM(
                    CASE
                        WHEN COALESCE(refund_by_work_item.refunded_rupiah, 0) <= 0
                            AND GREATEST(
                                work_items.subtotal_rupiah - GREATEST(
                                    COALESCE(payment_by_work_item.allocated_rupiah, 0)
                                    - COALESCE(refund_by_work_item.refunded_rupiah, 0),
                                    0
                                ),
                                0
                            ) <= 0
                        THEN 1
                        ELSE 0
                    END
                ), 0) as line_close_count
            ")
            ->groupBy('work_items.note_id');

        $legacyProjectionBase = DB::table('work_items')
            ->leftJoinSub($legacyAllocationTotals, 'legacy_allocation_totals', fn ($join) => $join->on('legacy_allocation_totals.note_id', '=', 'work_items.note_id'))
            ->leftJoinSub($legacyRefundTotals, 'legacy_refund_totals', fn ($join) => $join->on('legacy_refund_totals.note_id', '=', 'work_items.note_id'))
            ->selectRaw("
                work_items.note_id,
                work_items.id as work_item_id,
                work_items.line_no,
                work_items.subtotal_rupiah,
                COALESCE(legacy_allocation_totals.allocated_rupiah, 0) as note_allocated_rupiah,
                COALESCE(legacy_refund_totals.refunded_rupiah, 0) as note_refunded_rupiah,
                SUM(work_items.subtotal_rupiah) OVER (
                    PARTITION BY work_items.note_id
                    ORDER BY work_items.line_no, work_items.id
                    ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW
                ) as running_subtotal_rupiah
            ");

        $legacyAllocatedProjection = DB::query()
            ->fromSub($legacyProjectionBase, 'legacy_projection_base')
            ->selectRaw("
                note_id,
                work_item_id,
                line_no,
                subtotal_rupiah,
                note_refunded_rupiah,
                GREATEST(
                    LEAST(
                        note_allocated_rupiah - (running_subtotal_rupiah - subtotal_rupiah),
                        subtotal_rupiah
                    ),
                    0
                ) as allocated_rupiah
            ");

        $legacyRefundProjection = DB::query()
            ->fromSub($legacyAllocatedProjection, 'legacy_allocated_projection')
            ->selectRaw("
                note_id,
                work_item_id,
                line_no,
                subtotal_rupiah,
                allocated_rupiah,
                GREATEST(
                    LEAST(
                        note_refunded_rupiah - (
                            SUM(allocated_rupiah) OVER (
                                PARTITION BY note_id
                                ORDER BY line_no, work_item_id
                                ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW
                            ) - allocated_rupiah
                        ),
                        allocated_rupiah
                    ),
                    0
                ) as refunded_rupiah
            ");

        $legacyLineSummary = DB::query()
            ->fromSub($legacyRefundProjection, 'legacy_refund_projection')
            ->selectRaw("
                note_id,
                COALESCE(SUM(
                    CASE
                        WHEN refunded_rupiah > 0 THEN 1
                        ELSE 0
                    END
                ), 0) as line_refund_count,
                COALESCE(SUM(
                    CASE
                        WHEN refunded_rupiah <= 0
                            AND GREATEST(
                                subtotal_rupiah - GREATEST(allocated_rupiah - refunded_rupiah, 0),
                                0
                            ) > 0
                        THEN 1
                        ELSE 0
                    END
                ), 0) as line_open_count,
                COALESCE(SUM(
                    CASE
                        WHEN refunded_rupiah <= 0
                            AND GREATEST(
                                subtotal_rupiah - GREATEST(allocated_rupiah - refunded_rupiah, 0),
                                0
                            ) <= 0
                        THEN 1
                        ELSE 0
                    END
                ), 0) as line_close_count
            ")
            ->groupBy('note_id');

        return DB::table('notes')
            ->leftJoinSub($componentAllocationTotals, 'component_allocation_totals', fn ($join) => $join->on('component_allocation_totals.note_id', '=', 'notes.id'))
            ->leftJoinSub($legacyAllocationTotals, 'legacy_allocation_totals', fn ($join) => $join->on('legacy_allocation_totals.note_id', '=', 'notes.id'))
            ->leftJoinSub($componentRefundTotals, 'component_refund_totals', fn ($join) => $join->on('component_refund_totals.note_id', '=', 'notes.id'))
            ->leftJoinSub($legacyRefundTotals, 'legacy_refund_totals', fn ($join) => $join->on('legacy_refund_totals.note_id', '=', 'notes.id'))
            ->leftJoinSub($workSummary, 'work_summary', fn ($join) => $join->on('work_summary.note_id', '=', 'notes.id'))
            ->leftJoinSub($componentLineSummary, 'component_line_summary', fn ($join) => $join->on('component_line_summary.note_id', '=', 'notes.id'))
            ->leftJoinSub($legacyLineSummary, 'legacy_line_summary', fn ($join) => $join->on('legacy_line_summary.note_id', '=', 'notes.id'))
            ->where('notes.note_state', 'open')
            ->whereBetween('notes.transaction_date', [$criteria->previousDateText, $criteria->anchorDateText])
            ->when($criteria->search !== '', function ($query) use ($criteria): void {
                $query->where(function ($subQuery) use ($criteria): void {
                    $subQuery->where('notes.id', 'like', '%' . $criteria->search . '%')
                        ->orWhere('notes.customer_name', 'like', '%' . $criteria->search . '%')
                        ->orWhere('notes.customer_phone', 'like', '%' . $criteria->search . '%');
                });
            })
            ->select([
                'notes.id',
                'notes.customer_name',
                'notes.customer_phone',
                'notes.transaction_date',
                'notes.note_state',
                'notes.total_rupiah',
                DB::raw('COALESCE(component_allocation_totals.allocated_rupiah, legacy_allocation_totals.allocated_rupiah, 0) as allocated_rupiah'),
                DB::raw('COALESCE(component_refund_totals.refunded_rupiah, legacy_refund_totals.refunded_rupiah, 0) as refunded_rupiah'),
                DB::raw('COALESCE(work_summary.open_count, 0) as open_count'),
                DB::raw('COALESCE(work_summary.done_count, 0) as done_count'),
                DB::raw('COALESCE(work_summary.canceled_count, 0) as canceled_count'),
                DB::raw("
                    CASE
                        WHEN component_allocation_totals.note_id IS NOT NULL
                            OR component_refund_totals.note_id IS NOT NULL
                        THEN COALESCE(component_line_summary.line_open_count, 0)
                        ELSE COALESCE(legacy_line_summary.line_open_count, 0)
                    END as line_open_count
                "),
                DB::raw("
                    CASE
                        WHEN component_allocation_totals.note_id IS NOT NULL
                            OR component_refund_totals.note_id IS NOT NULL
                        THEN COALESCE(component_line_summary.line_close_count, 0)
                        ELSE COALESCE(legacy_line_summary.line_close_count, 0)
                    END as line_close_count
                "),
                DB::raw("
                    CASE
                        WHEN component_allocation_totals.note_id IS NOT NULL
                            OR component_refund_totals.note_id IS NOT NULL
                        THEN COALESCE(component_line_summary.line_refund_count, 0)
                        ELSE COALESCE(legacy_line_summary.line_refund_count, 0)
                    END as line_refund_count
                "),
            ])
            ->orderByDesc('notes.transaction_date')
            ->orderByDesc('notes.id')
            ->get()
            ->all();
    }
}
