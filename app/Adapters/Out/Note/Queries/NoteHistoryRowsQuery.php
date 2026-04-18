<?php

declare(strict_types=1);

namespace App\Adapters\Out\Note\Queries;

use Illuminate\Support\Facades\DB;

final class NoteHistoryRowsQuery
{
    public function __construct(
        private readonly NoteHistoryAggregationSubqueries $aggregations,
        private readonly NoteHistoryComponentLineSummarySubquery $componentLineSummary,
        private readonly NoteHistoryLegacyLineSummarySubquery $legacyLineSummary,
        private readonly NoteHistorySelectColumns $selectColumns,
        private readonly NoteHistorySearchScope $searchScope,
    ) {
    }

    /**
     * @return array<int, object>
     */
    public function fetch(string $dateFrom, string $dateTo, string $search, bool $openOnly): array
    {
        $query = DB::table('notes')
            ->leftJoinSub($this->aggregations->componentAllocationTotals(), 'component_allocation_totals', fn ($join) => $join->on('component_allocation_totals.note_id', '=', 'notes.id'))
            ->leftJoinSub($this->aggregations->legacyAllocationTotals(), 'legacy_allocation_totals', fn ($join) => $join->on('legacy_allocation_totals.note_id', '=', 'notes.id'))
            ->leftJoinSub($this->aggregations->componentRefundTotals(), 'component_refund_totals', fn ($join) => $join->on('component_refund_totals.note_id', '=', 'notes.id'))
            ->leftJoinSub($this->aggregations->legacyRefundTotals(), 'legacy_refund_totals', fn ($join) => $join->on('legacy_refund_totals.note_id', '=', 'notes.id'))
            ->leftJoinSub($this->aggregations->workSummary(), 'work_summary', fn ($join) => $join->on('work_summary.note_id', '=', 'notes.id'))
            ->leftJoinSub($this->componentLineSummary->build(), 'component_line_summary', fn ($join) => $join->on('component_line_summary.note_id', '=', 'notes.id'))
            ->leftJoinSub($this->legacyLineSummary->build(), 'legacy_line_summary', fn ($join) => $join->on('legacy_line_summary.note_id', '=', 'notes.id'))
            ->whereBetween('notes.transaction_date', [$dateFrom, $dateTo]);

        if ($openOnly) {
            $query->where('notes.note_state', 'open');
        }

        $this->searchScope->apply($query, $search);

        return $query->select($this->selectColumns->all())
            ->orderByDesc('notes.transaction_date')
            ->orderByDesc('notes.id')
            ->get()
            ->all();
    }
}
