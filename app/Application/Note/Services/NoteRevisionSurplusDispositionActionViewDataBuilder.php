<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Application\Note\DTO\NoteRevisionSurplusDisposition;
use App\Ports\Out\Note\NoteRevisionSurplusDispositionReaderPort;
use App\Ports\Out\Note\NoteRevisionSurplusRefundDueSourceReaderPort;

final class NoteRevisionSurplusDispositionActionViewDataBuilder
{
    public function __construct(
        private readonly NoteRevisionSurplusDispositionReaderPort $reader,
        private readonly NoteRevisionSurplusRefundDueSourceReaderPort $refundDueSources,
    ) {
    }

    /**
     * @return array{
     *   has_pending_refund_due_action: bool,
     *   pending_items: list<array<string, mixed>>,
     *   has_pending_refund_paid_action: bool,
     *   refund_paid_items: list<array<string, mixed>>
     * }
     */
    public function build(string $noteRootId): array
    {
        $pendingItems = [];

        foreach ($this->reader->findPendingByNoteRootId($noteRootId) as $pending) {
            if ($pending->unresolvedPendingRupiah <= 0) {
                continue;
            }

            $pendingItems[] = [
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

        $refundPaidItems = [];

        foreach ($this->refundDueSources->findActiveRefundDueByNoteRootId($noteRootId) as $source) {
            if ($source->remainingRefundDueRupiah <= 0) {
                continue;
            }

            $refundPaidItems[] = [
                'note_revision_surplus_disposition_id' => $source->dispositionId,
                'note_revision_settlement_id' => $source->noteRevisionSettlementId,
                'note_revision_id' => $source->noteRevisionId,
                'note_root_id' => $source->noteRootId,
                'refund_due_rupiah' => $source->refundDueRupiah,
                'active_refund_paid_rupiah' => $source->activeRefundPaidRupiah,
                'remaining_refund_due_rupiah' => $source->remainingRefundDueRupiah,
                'amount_default_rupiah' => $source->remainingRefundDueRupiah,
                'reason_required' => true,
            ];
        }

        return [
            'has_pending_refund_due_action' => $pendingItems !== [],
            'pending_items' => $pendingItems,
            'has_pending_refund_paid_action' => $refundPaidItems !== [],
            'refund_paid_items' => $refundPaidItems,
        ];
    }
}
