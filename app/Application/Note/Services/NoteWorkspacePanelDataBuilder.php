<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Ports\Out\Note\NoteReaderPort;

final class NoteWorkspacePanelDataBuilder
{
    public function __construct(
        private readonly NoteReaderPort $notes,
        private readonly NoteOperationalRowSettlementProjector $rowSettlements,
        private readonly NoteDetailRowMapper $rowMapper,
        private readonly NoteLineSummaryBuilder $lineSummary,
    ) {
    }

    /**
     * @return array{
     *   note_header: array<string, mixed>,
     *   note_totals: array<string, int>,
     *   line_summary: array{
     *     open_count: int,
     *     close_count: int,
     *     refund_count: int,
     *     summary_label: string
     *   },
     *   rows: list<array<string, mixed>>
     * }|null
     */
    public function build(string $noteId): ?array
    {
        $note = $this->notes->getById(trim($noteId));

        if ($note === null) {
            return null;
        }

        $settlements = $this->rowSettlements->build($note->id(), $note->workItems());
        $rows = $this->rowMapper->map($note->workItems(), $settlements);
        $lineSummary = $this->lineSummary->build($rows);

        $totalAllocated = 0;
        $totalRefunded = 0;
        $totalNetPaid = 0;
        $totalOutstanding = 0;

        foreach ($rows as $row) {
            $totalAllocated += (int) ($row['allocated_rupiah'] ?? 0);
            $totalRefunded += (int) ($row['refunded_rupiah'] ?? 0);
            $totalNetPaid += (int) ($row['net_paid_rupiah'] ?? 0);
            $totalOutstanding += (int) ($row['outstanding_rupiah'] ?? 0);
        }

        return [
            'note_header' => [
                'id' => $note->id(),
                'customer_name' => $note->customerName(),
                'customer_phone' => $note->customerPhone(),
                'transaction_date' => $note->transactionDate()->format('Y-m-d'),
            ],
            'note_totals' => [
                'grand_total_rupiah' => $note->totalRupiah()->amount(),
                'total_allocated_rupiah' => $totalAllocated,
                'total_refunded_rupiah' => $totalRefunded,
                'net_paid_rupiah' => $totalNetPaid,
                'outstanding_rupiah' => $totalOutstanding,
            ],
            'line_summary' => $lineSummary,
            'rows' => $rows,
        ];
    }
}
