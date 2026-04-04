<?php

declare(strict_types=1);

namespace App\Adapters\Out\Note\Queries;

use Illuminate\Support\Facades\DB;

final class AdminNoteHistoryBaseQuery
{
    /**
     * @return array<int, object>
     */
    public function fetch(AdminNoteHistoryCriteria $criteria): array
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

        return DB::table('notes')
            ->leftJoinSub($componentAllocationTotals, 'component_allocation_totals', fn ($join) => $join->on('component_allocation_totals.note_id', '=', 'notes.id'))
            ->leftJoinSub($legacyAllocationTotals, 'legacy_allocation_totals', fn ($join) => $join->on('legacy_allocation_totals.note_id', '=', 'notes.id'))
            ->leftJoinSub($componentRefundTotals, 'component_refund_totals', fn ($join) => $join->on('component_refund_totals.note_id', '=', 'notes.id'))
            ->leftJoinSub($legacyRefundTotals, 'legacy_refund_totals', fn ($join) => $join->on('legacy_refund_totals.note_id', '=', 'notes.id'))
            ->leftJoinSub($workSummary, 'work_summary', fn ($join) => $join->on('work_summary.note_id', '=', 'notes.id'))
            ->whereBetween('notes.transaction_date', [$criteria->dateFromText, $criteria->dateToText])
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
            ])
            ->orderByDesc('notes.transaction_date')
            ->orderByDesc('notes.id')
            ->get()
            ->all();
    }
}
