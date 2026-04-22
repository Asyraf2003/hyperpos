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
        private readonly NoteWorkspacePanelPayloadFactory $payloads,
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
            $lines = $revision->lines();

            return $this->payloads->build(
                $note->id(),
                (string) $revision->customerName(),
                $revision->customerPhone(),
                $revision->transactionDate()->format('Y-m-d'),
                $revision->grandTotalRupiah(),
                $this->revisionRows->map(
                    $lines,
                    $this->revisionSettlements->build($note->id(), $lines),
                ),
            );
        }

        $rows = $this->rootRows->map(
            $note->workItems(),
            $this->rootSettlements->build($note->id(), $note->workItems()),
        );

        return $this->payloads->build(
            $note->id(),
            $note->customerName(),
            $note->customerPhone(),
            $note->transactionDate()->format('Y-m-d'),
            $note->totalRupiah()->amount(),
            $rows,
        );
    }
}
