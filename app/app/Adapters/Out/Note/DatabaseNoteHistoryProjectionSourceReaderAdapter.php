<?php

declare(strict_types=1);

namespace App\Adapters\Out\Note;

use App\Adapters\Out\Note\Queries\NoteHistoryAggregationSubqueries;
use App\Adapters\Out\Note\Queries\NoteHistoryComponentLineSummarySubquery;
use App\Adapters\Out\Note\Queries\NoteHistoryLegacyLineSummarySubquery;
use App\Ports\Out\Note\NoteHistoryProjectionSourceReaderPort;
use Illuminate\Support\Facades\DB;

final class DatabaseNoteHistoryProjectionSourceReaderAdapter implements NoteHistoryProjectionSourceReaderPort
{
    public function __construct(
        private readonly NoteHistoryAggregationSubqueries $aggregations,
        private readonly NoteHistoryComponentLineSummarySubquery $componentLineSummary,
        private readonly NoteHistoryLegacyLineSummarySubquery $legacyLineSummary,
    ) {
    }

    public function findByNoteId(string $noteId): ?array
    {
        $normalizedNoteId = trim($noteId);

        if ($normalizedNoteId === '') {
            return null;
        }

        $componentAllocationTotals = $this->aggregations->componentAllocationTotals();
        $legacyAllocationTotals = $this->aggregations->legacyAllocationTotals();
        $componentRefundTotals = $this->aggregations->componentRefundTotals();
        $legacyRefundTotals = $this->aggregations->legacyRefundTotals();

        $row = DB::table('notes')
            ->leftJoinSub($componentAllocationTotals, 'component_allocation_totals', fn ($join) => $join->on('component_allocation_totals.note_id', '=', 'notes.id'))
            ->leftJoinSub($legacyAllocationTotals, 'legacy_allocation_totals', fn ($join) => $join->on('legacy_allocation_totals.note_id', '=', 'notes.id'))
            ->leftJoinSub($componentRefundTotals, 'component_refund_totals', fn ($join) => $join->on('component_refund_totals.note_id', '=', 'notes.id'))
            ->leftJoinSub($legacyRefundTotals, 'legacy_refund_totals', fn ($join) => $join->on('legacy_refund_totals.note_id', '=', 'notes.id'))
            ->leftJoinSub($this->componentLineSummary->build(), 'component_line_summary', fn ($join) => $join->on('component_line_summary.note_id', '=', 'notes.id'))
            ->leftJoinSub($this->legacyLineSummary->build(), 'legacy_line_summary', fn ($join) => $join->on('legacy_line_summary.note_id', '=', 'notes.id'))
            ->where('notes.id', $normalizedNoteId)
            ->first([
                'notes.id as note_id',
                'notes.transaction_date',
                'notes.note_state',
                'notes.customer_name',
                'notes.customer_phone',
                'notes.total_rupiah',
                DB::raw('COALESCE(component_allocation_totals.allocated_rupiah, legacy_allocation_totals.allocated_rupiah, 0) as allocated_rupiah'),
                DB::raw('COALESCE(component_refund_totals.refunded_rupiah, legacy_refund_totals.refunded_rupiah, 0) as refunded_rupiah'),
                DB::raw("CASE WHEN component_allocation_totals.note_id IS NOT NULL OR component_refund_totals.note_id IS NOT NULL THEN COALESCE(component_line_summary.line_open_count, 0) ELSE COALESCE(legacy_line_summary.line_open_count, 0) END as line_open_count"),
                DB::raw("CASE WHEN component_allocation_totals.note_id IS NOT NULL OR component_refund_totals.note_id IS NOT NULL THEN COALESCE(component_line_summary.line_close_count, 0) ELSE COALESCE(legacy_line_summary.line_close_count, 0) END as line_close_count"),
                DB::raw("CASE WHEN component_allocation_totals.note_id IS NOT NULL OR component_refund_totals.note_id IS NOT NULL THEN COALESCE(component_line_summary.line_refund_count, 0) ELSE COALESCE(legacy_line_summary.line_refund_count, 0) END as line_refund_count"),
            ]);

        if ($row === null) {
            return null;
        }

        return [
            'note_id' => (string) $row->note_id,
            'transaction_date' => (string) $row->transaction_date,
            'note_state' => (string) $row->note_state,
            'customer_name' => (string) $row->customer_name,
            'customer_phone' => $this->nullableString($row->customer_phone),
            'total_rupiah' => (int) $row->total_rupiah,
            'allocated_rupiah' => (int) $row->allocated_rupiah,
            'refunded_rupiah' => (int) $row->refunded_rupiah,
            'line_open_count' => (int) $row->line_open_count,
            'line_close_count' => (int) $row->line_close_count,
            'line_refund_count' => (int) $row->line_refund_count,
        ];
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $string = trim((string) $value);

        return $string === '' ? null : $string;
    }
}
