<?php

declare(strict_types=1);

namespace App\Adapters\Out\Note\Queries;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

final class NoteHistoryComponentLineSummarySubquery
{
    public function __construct(
        private readonly NoteHistoryAggregationSubqueries $aggregations,
    ) {
    }

    public function build(): Builder
    {
        $paymentByWorkItem = $this->aggregations->componentPaymentByWorkItem();
        $refundByWorkItem = $this->aggregations->componentRefundByWorkItem();

        return DB::table('work_items')
            ->leftJoinSub($paymentByWorkItem, 'payment_by_work_item', fn ($join) => $join->on('payment_by_work_item.note_id', '=', 'work_items.note_id')->on('payment_by_work_item.work_item_id', '=', 'work_items.id'))
            ->leftJoinSub($refundByWorkItem, 'refund_by_work_item', fn ($join) => $join->on('refund_by_work_item.note_id', '=', 'work_items.note_id')->on('refund_by_work_item.work_item_id', '=', 'work_items.id'))
            ->selectRaw("work_items.note_id, COALESCE(SUM(CASE WHEN COALESCE(refund_by_work_item.refunded_rupiah, 0) > 0 THEN 1 ELSE 0 END), 0) as line_refund_count, COALESCE(SUM(CASE WHEN COALESCE(refund_by_work_item.refunded_rupiah, 0) <= 0 AND GREATEST(work_items.subtotal_rupiah - GREATEST(COALESCE(payment_by_work_item.allocated_rupiah, 0) - COALESCE(refund_by_work_item.refunded_rupiah, 0), 0), 0) > 0 THEN 1 ELSE 0 END), 0) as line_open_count, COALESCE(SUM(CASE WHEN COALESCE(refund_by_work_item.refunded_rupiah, 0) <= 0 AND GREATEST(work_items.subtotal_rupiah - GREATEST(COALESCE(payment_by_work_item.allocated_rupiah, 0) - COALESCE(refund_by_work_item.refunded_rupiah, 0), 0), 0) <= 0 THEN 1 ELSE 0 END), 0) as line_close_count")
            ->groupBy('work_items.note_id');
    }
}
