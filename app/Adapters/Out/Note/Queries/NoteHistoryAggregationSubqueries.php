<?php

declare(strict_types=1);

namespace App\Adapters\Out\Note\Queries;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

final class NoteHistoryAggregationSubqueries
{
    public function componentAllocationTotals(): Builder
    {
        return DB::table('payment_component_allocations')
            ->selectRaw('note_id, COALESCE(SUM(allocated_amount_rupiah), 0) as allocated_rupiah')
            ->groupBy('note_id');
    }

    public function legacyAllocationTotals(): Builder
    {
        return DB::table('payment_allocations')
            ->selectRaw('note_id, COALESCE(SUM(amount_rupiah), 0) as allocated_rupiah')
            ->groupBy('note_id');
    }

    public function componentRefundTotals(): Builder
    {
        return DB::table('refund_component_allocations')
            ->selectRaw('note_id, COALESCE(SUM(refunded_amount_rupiah), 0) as refunded_rupiah')
            ->groupBy('note_id');
    }

    public function legacyRefundTotals(): Builder
    {
        return DB::table('customer_refunds')
            ->selectRaw('note_id, COALESCE(SUM(amount_rupiah), 0) as refunded_rupiah')
            ->groupBy('note_id');
    }

    public function workSummary(): Builder
    {
        return DB::table('work_items')
            ->selectRaw("note_id, COALESCE(SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END), 0) as open_count, COALESCE(SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END), 0) as done_count, COALESCE(SUM(CASE WHEN status = 'canceled' THEN 1 ELSE 0 END), 0) as canceled_count")
            ->groupBy('note_id');
    }

    public function componentPaymentByWorkItem(): Builder
    {
        return DB::table('payment_component_allocations')
            ->selectRaw('note_id, work_item_id, COALESCE(SUM(allocated_amount_rupiah), 0) as allocated_rupiah')
            ->groupBy('note_id', 'work_item_id');
    }

    public function componentRefundByWorkItem(): Builder
    {
        return DB::table('refund_component_allocations')
            ->selectRaw('note_id, work_item_id, COALESCE(SUM(refunded_amount_rupiah), 0) as refunded_rupiah')
            ->groupBy('note_id', 'work_item_id');
    }
}
