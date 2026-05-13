<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Application\Note\DTO\NoteRevisionSurplusDisposition;
use App\Ports\Out\Note\NoteRevisionSurplusDispositionReaderPort;

final class NoteRevisionSurplusDispositionActionViewDataBuilder
{
    public function __construct(
        private readonly NoteRevisionSurplusDispositionReaderPort $reader,
    ) {
    }

    /** @return array{has_pending_refund_due_action: bool, pending_items: list<array<string, mixed>>} */
    public function build(string $noteRootId): array
    {
        $items = [];

        foreach ($this->reader->findPendingByNoteRootId($noteRootId) as $pending) {
            if ($pending->unresolvedPendingRupiah <= 0) {
                continue;
            }

            $items[] = [
                'note_revision_settlement_id' => $pending->noteRevisionSettlementId,
                'note_revision_id' => $pending->noteRevisionId,
                'note_root_id' => $pending->noteRootId,
                'surplus_rupiah' => $pending->surplusRupiah,
                'active_disposition_rupiah' => $pending->activeDispositionRupiah,
                'unresolved_pending_rupiah' => $pending->unresolvedPendingRupiah,
                'disposition_type' => NoteRevisionSurplusDisposition::TYPE_REFUND_DUE,
                'amount_default_rupiah' => $pending->unresolvedPendingRupiah,
                'reason_required' => true,
            ];
        }

        return [
            'has_pending_refund_due_action' => $items !== [],
            'pending_items' => $items,
        ];
    }
}
