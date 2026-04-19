<?php

declare(strict_types=1);

namespace App\Adapters\Out\Note\Queries;

use App\Application\Note\Services\WorkItemOperationalStatusResolver;

final class AdminNoteHistoryRowMapper
{
    public function __construct(
        private readonly CashierNoteHistoryValueFormatter $formatter,
    ) {
    }

    /**
     * @param array<int, object> $rows
     * @return list<array<string, mixed>>
     */
    public function map(array $rows, AdminNoteHistoryCriteria $criteria): array
    {
        $items = [];

        foreach ($rows as $row) {
            $grandTotal = (int) $row->total_rupiah;
            $allocated = (int) ($row->allocated_rupiah ?? 0);
            $refunded = (int) ($row->refunded_rupiah ?? 0);
            $netPaid = max($allocated - $refunded, 0);
            $outstanding = max($grandTotal - $netPaid, 0);

            $lineOpenCount = (int) ($row->line_open_count ?? 0);
            $lineCloseCount = (int) ($row->line_close_count ?? 0);
            $lineRefundCount = (int) ($row->line_refund_count ?? 0);

            if (! $this->matchesLineStatusFilter(
                $criteria->lineStatus,
                $lineOpenCount,
                $lineCloseCount,
                $lineRefundCount
            )) {
                continue;
            }

            $items[] = [
                'note_id' => (string) $row->id,
                'transaction_date' => (string) $row->transaction_date,
                'note_number' => (string) $row->id,
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
                'action_url' => route('admin.notes.show', ['noteId' => (string) $row->id]),
            ];
        }

        return $items;
    }

    private function matchesLineStatusFilter(
        string $filter,
        int $lineOpenCount,
        int $lineCloseCount,
        int $lineRefundCount
    ): bool {
        return match ($filter) {
            WorkItemOperationalStatusResolver::STATUS_OPEN => $lineOpenCount > 0,
            WorkItemOperationalStatusResolver::STATUS_CLOSE => $lineCloseCount > 0,
            WorkItemOperationalStatusResolver::STATUS_REFUND => $lineRefundCount > 0,
            '' => true,
            default => true,
        };
    }
}
