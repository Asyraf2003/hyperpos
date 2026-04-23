<?php

declare(strict_types=1);

namespace App\Adapters\Out\Note\Queries;

use App\Application\Note\Services\WorkItemOperationalStatusResolver;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

final class CashierNoteHistoryBaseQuery
{
    public function __construct(
        private readonly NoteHistoryAggregationSubqueries $aggregations,
    ) {
    }

    public function paginate(CashierNoteHistoryCriteria $criteria): LengthAwarePaginator
    {
        $query = DB::table('note_history_projection')
            ->leftJoinSub(
                $this->aggregations->workSummary(),
                'work_summary',
                fn ($join) => $join->on('work_summary.note_id', '=', 'note_history_projection.note_id')
            )
            ->select([
                'note_history_projection.note_id as id',
                'note_history_projection.transaction_date',
                'note_history_projection.customer_name',
                'note_history_projection.customer_phone',
                'note_history_projection.total_rupiah',
                DB::raw('note_history_projection.net_paid_rupiah as allocated_rupiah'),
                DB::raw('0 as refunded_rupiah'),
                'note_history_projection.line_open_count',
                'note_history_projection.line_close_count',
                'note_history_projection.line_refund_count',
                DB::raw('COALESCE(work_summary.open_count, 0) as open_count'),
                DB::raw('COALESCE(work_summary.done_count, 0) as done_count'),
                DB::raw('COALESCE(work_summary.canceled_count, 0) as canceled_count'),
            ])
            ->whereBetween('note_history_projection.transaction_date', [
                $criteria->previousDateText,
                $criteria->anchorDateText,
            ]);

        $query = $this->applySearch($query, $criteria->search);
        $query = $this->applyLineStatus($query, $criteria->lineStatus);

        return $query
            ->orderByDesc('note_history_projection.transaction_date')
            ->orderByDesc('note_history_projection.note_id')
            ->paginate($criteria->perPage, ['*'], 'page', $criteria->page);
    }

    /**
     * @return array<int, object>
     */
    public function fetch(CashierNoteHistoryCriteria $criteria): array
    {
        return $this->paginate($criteria)->items();
    }

    private function applySearch(Builder $query, string $search): Builder
    {
        if ($search === '') {
            return $query;
        }

        $normalizedSearch = mb_strtolower(trim($search), 'UTF-8');

        return $query->where(function (Builder $builder) use ($search, $normalizedSearch): void {
            $builder
                ->where('note_history_projection.note_id', 'like', '%' . $search . '%')
                ->orWhere('note_history_projection.customer_name', 'like', '%' . $search . '%')
                ->orWhere('note_history_projection.customer_name_normalized', 'like', '%' . $normalizedSearch . '%')
                ->orWhere('note_history_projection.customer_phone', 'like', '%' . $search . '%');
        });
    }

    private function applyLineStatus(Builder $query, string $lineStatus): Builder
    {
        return match ($lineStatus) {
            WorkItemOperationalStatusResolver::STATUS_OPEN => $query->where('note_history_projection.has_open_lines', true),
            WorkItemOperationalStatusResolver::STATUS_CLOSE => $query->where('note_history_projection.has_close_lines', true),
            WorkItemOperationalStatusResolver::STATUS_REFUND => $query->where('note_history_projection.has_refund_lines', true),
            default => $query,
        };
    }
}
