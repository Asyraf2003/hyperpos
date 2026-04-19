<?php

declare(strict_types=1);

namespace App\Adapters\Out\Note\Queries;

use Illuminate\Support\Facades\DB;

final class NoteHistorySelectColumns
{
    /**
     * @return array<int, mixed>
     */
    public function all(): array
    {
        return [
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
            DB::raw("CASE WHEN component_allocation_totals.note_id IS NOT NULL OR component_refund_totals.note_id IS NOT NULL THEN COALESCE(component_line_summary.line_open_count, 0) ELSE COALESCE(legacy_line_summary.line_open_count, 0) END as line_open_count"),
            DB::raw("CASE WHEN component_allocation_totals.note_id IS NOT NULL OR component_refund_totals.note_id IS NOT NULL THEN COALESCE(component_line_summary.line_close_count, 0) ELSE COALESCE(legacy_line_summary.line_close_count, 0) END as line_close_count"),
            DB::raw("CASE WHEN component_allocation_totals.note_id IS NOT NULL OR component_refund_totals.note_id IS NOT NULL THEN COALESCE(component_line_summary.line_refund_count, 0) ELSE COALESCE(legacy_line_summary.line_refund_count, 0) END as line_refund_count"),
        ];
    }
}
