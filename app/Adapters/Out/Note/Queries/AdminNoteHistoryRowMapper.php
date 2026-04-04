<?php

declare(strict_types=1);

namespace App\Adapters\Out\Note\Queries;

use App\Application\Note\Services\NotePaymentStatusResolver;

final class AdminNoteHistoryRowMapper
{
    public function __construct(
        private readonly NotePaymentStatusResolver $paymentStatuses,
        private readonly CashierNoteHistoryValueFormatter $formatter,
        private readonly AdminNoteHistoryEditabilityResolver $editability,
        private readonly AdminNoteHistoryWorkSummaryFilter $workSummaryFilter,
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
            $paymentStatus = $this->paymentStatuses->resolve($grandTotal, $netPaid);
            $noteState = (string) ($row->note_state ?? 'open');
            $openCount = (int) ($row->open_count ?? 0);
            $doneCount = (int) ($row->done_count ?? 0);
            $canceledCount = (int) ($row->canceled_count ?? 0);
            $editabilityKey = $this->editability->key($noteState, $paymentStatus);

            if ($criteria->paymentStatus !== '' && $paymentStatus !== $criteria->paymentStatus) {
                continue;
            }

            if ($criteria->editability !== '' && $editabilityKey !== $criteria->editability) {
                continue;
            }

            if (! $this->workSummaryFilter->matches($criteria->workSummary, $openCount, $doneCount, $canceledCount)) {
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
                'payment_status_label' => $this->formatter->paymentStatusLabel($paymentStatus),
                'work_status_label' => $this->formatter->workSummary($openCount, $doneCount, $canceledCount),
                'editability_key' => $editabilityKey,
                'editability_label' => $this->editability->label($editabilityKey),
                'action_label' => 'Buka Detail',
                'action_url' => route('admin.notes.show', ['noteId' => (string) $row->id]),
            ];
        }

        return $items;
    }
}
