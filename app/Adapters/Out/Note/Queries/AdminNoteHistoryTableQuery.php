<?php

declare(strict_types=1);

namespace App\Adapters\Out\Note\Queries;

use App\Application\Note\Services\WorkItemOperationalStatusResolver;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

final class AdminNoteHistoryTableQuery
{
    public function __construct(
        private readonly CashierNoteHistoryValueFormatter $formatter,
    ) {
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{
     *   filters: array<string, mixed>,
     *   items: list<array<string, mixed>>,
     *   pagination: array<string, int>,
     *   summary: array{label: string}
     * }
     */
    public function get(array $filters): array
    {
        $criteria = AdminNoteHistoryCriteria::fromFilters($filters);

        $builder = DB::table('note_history_projection')
            ->whereBetween('transaction_date', [$criteria->dateFromText, $criteria->dateToText]);

        $builder = $this->applySearch($builder, $criteria->search);
        $builder = $this->applyLineStatusFilter($builder, $criteria->lineStatus);

        $paginator = $builder
            ->orderByDesc('transaction_date')
            ->orderByDesc('note_id')
            ->paginate($criteria->perPage, ['*'], 'page', $criteria->page);

        $items = array_map(
            fn (object $row): array => $this->toItem($row),
            $paginator->items(),
        );

        return [
            'filters' => [
                'date_from' => $criteria->dateFromText,
                'date_to' => $criteria->dateToText,
                'search' => $criteria->search,
                'line_status' => $criteria->lineStatus,
            ],
            'items' => $items,
            'pagination' => [
                'page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
            'summary' => [
                'label' => sprintf(
                    'Daftar nota admin %s sampai %s.',
                    $criteria->dateFromText,
                    $criteria->dateToText,
                ),
            ],
        ];
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

    private function applyLineStatusFilter(Builder $query, string $lineStatus): Builder
    {
        return match ($lineStatus) {
            WorkItemOperationalStatusResolver::STATUS_OPEN => $query->where('has_open_lines', true),
            WorkItemOperationalStatusResolver::STATUS_CLOSE => $query->where('has_close_lines', true),
            WorkItemOperationalStatusResolver::STATUS_REFUND => $query->where('has_refund_lines', true),
            default => $query,
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function toItem(object $row): array
    {
        $grandTotal = (int) $row->total_rupiah;
        $netPaid = (int) $row->net_paid_rupiah;
        $outstanding = (int) $row->outstanding_rupiah;

        $lineOpenCount = (int) $row->line_open_count;
        $lineCloseCount = (int) $row->line_close_count;
        $lineRefundCount = (int) $row->line_refund_count;

        return [
            'note_id' => (string) $row->note_id,
            'transaction_date' => (string) $row->transaction_date,
            'note_number' => (string) $row->note_id,
            'customer_name' => $this->formatter->customerLabel(
                (string) $row->customer_name,
                $row->customer_phone !== null ? (string) $row->customer_phone : null,
            ),
            'grand_total_text' => $this->formatter->rupiah($grandTotal),
            'total_paid_text' => $this->formatter->rupiah($netPaid),
            'outstanding_text' => $this->formatter->rupiah($outstanding),
            'line_summary_label' => $this->formatter->lineSummary(
                $lineOpenCount,
                $lineCloseCount,
                $lineRefundCount,
            ),
            'line_summary_counts' => [
                'open' => $lineOpenCount,
                'close' => $lineCloseCount,
                'refund' => $lineRefundCount,
            ],
            'action_label' => 'Pilih',
            'action_url' => route('admin.notes.show', ['noteId' => (string) $row->note_id]),
        ];
    }
}
