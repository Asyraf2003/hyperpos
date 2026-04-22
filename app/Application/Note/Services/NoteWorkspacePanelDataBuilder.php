<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Application\Note\Services\CurrentRevision\CurrentRevisionDetailRowMapper;
use App\Application\Note\Services\CurrentRevision\CurrentRevisionRowSettlementProjector;
use App\Ports\Out\Note\NoteReaderPort;

final class NoteWorkspacePanelDataBuilder
{
    public function __construct(
        private readonly NoteReaderPort $notes,
        private readonly NoteCurrentRevisionResolver $revisions,
        private readonly CurrentRevisionRowSettlementProjector $revisionSettlements,
        private readonly CurrentRevisionDetailRowMapper $revisionRows,
        private readonly NoteOperationalRowSettlementProjector $rootSettlements,
        private readonly NoteDetailRowMapper $rootRows,
        private readonly NoteLineSummaryBuilder $lineSummary,
    ) {
    }

    public function build(string $noteId): ?array
    {
        $note = $this->notes->getById(trim($noteId));

        if ($note === null) {
            return null;
        }

        if ($this->revisions->hasRevision($note->id())) {
            $revision = $this->revisions->resolveOrFail($note->id());
            $rows = $this->revisionRows->map(
                $revision->lines(),
                $this->revisionSettlements->build($note->id(), $revision->lines()),
            );

            return $this->payload(
                $note->id(),
                (string) $revision->customerName(),
                $revision->customerPhone(),
                $revision->transactionDate()->format('Y-m-d'),
                $revision->grandTotalRupiah(),
                $rows,
            );
        }

        $rows = $this->rootRows->map(
            $note->workItems(),
            $this->rootSettlements->build($note->id(), $note->workItems()),
        );

        return $this->payload(
            $note->id(),
            $note->customerName(),
            $note->customerPhone(),
            $note->transactionDate()->format('Y-m-d'),
            $note->totalRupiah()->amount(),
            $rows,
        );
    }

    private function payload(
        string $noteId,
        string $customerName,
        ?string $customerPhone,
        string $transactionDate,
        int $grandTotal,
        array $rows,
    ): array {
        $summary = $this->lineSummary->build($rows);

        $allocated = 0;
        $refunded = 0;
        $netPaid = 0;
        $outstanding = 0;

        foreach ($rows as $row) {
            $allocated += (int) ($row['allocated_rupiah'] ?? 0);
            $refunded += (int) ($row['refunded_rupiah'] ?? 0);
            $netPaid += (int) ($row['net_paid_rupiah'] ?? 0);
            $outstanding += (int) ($row['outstanding_rupiah'] ?? 0);
        }

        return [
            'note_header' => [
                'id' => $noteId,
                'customer_name' => $customerName,
                'customer_phone' => $customerPhone,
                'transaction_date' => $transactionDate,
            ],
            'note_totals' => [
                'grand_total_rupiah' => $grandTotal,
                'total_allocated_rupiah' => $allocated,
                'total_refunded_rupiah' => $refunded,
                'net_paid_rupiah' => $netPaid,
                'outstanding_rupiah' => $outstanding,
            ],
            'line_summary' => $summary,
            'rows' => $rows,
        ];
    }
}
