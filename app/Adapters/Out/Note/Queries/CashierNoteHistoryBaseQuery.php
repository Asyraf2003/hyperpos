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
        private readonly NoteHistoryRowsQuery $rowsQuery,
    ) {
    }

    public function paginate(CashierNoteHistoryCriteria $criteria): LengthAwarePaginator
    {
        $query = DB::table('note_history_projection')
            ->select([
                'note_id as id',
                'transaction_date',
                'customer_name',
                'customer_phone',
                'total_rupiah',
                DB::raw('net_paid_rupiah as allocated_rupiah'),
                DB::raw('0 as refunded_rupiah'),
                'line_open_count',
                'line_close_count',
                'line_refund_count',
                DB::raw('0 as open_count'),
                DB::raw('0 as done_count'),
                DB::raw('0 as canceled_count'),
            ])
            ->whereBetween('transaction_date', [
                $criteria->previousDateText,
                $criteria->anchorDateText,
            ]);

        $query = $this->applySearch($query, $criteria->search);
        $query = $this->applyLineStatus($query, $criteria->lineStatus);

        return $query
            ->orderByDesc('transaction_date')
            ->orderByDesc('note_id')
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
                ->where('note_id', 'like', '%' . $search . '%')
                ->orWhere('customer_name', 'like', '%' . $search . '%')
                ->orWhere('customer_name_normalized', 'like', '%' . $normalizedSearch . '%')
                ->orWhere('customer_phone', 'like', '%' . $search . '%');
        });
    }

    private function applyLineStatus(Builder $query, string $lineStatus): Builder
    {
        return match ($lineStatus) {
            WorkItemOperationalStatusResolver::STATUS_OPEN => $query->where('has_open_lines', true),
            WorkItemOperationalStatusResolver::STATUS_CLOSE => $query->where('has_close_lines', true),
            WorkItemOperationalStatusResolver::STATUS_REFUND => $query->where('has_refund_lines', true),
            default => $query,
        };
    }
}
