<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Ports\Out\Note\NoteReaderPort;

final class NoteWorkspacePanelDataBuilder
{
    public function __construct(
        private readonly NoteReaderPort $notes,
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
