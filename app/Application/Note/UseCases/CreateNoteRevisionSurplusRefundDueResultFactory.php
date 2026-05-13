<?php

declare(strict_types=1);

namespace App\Application\Note\UseCases;

use App\Application\Note\DTO\NoteRevisionSurplusDisposition;
use App\Application\Note\DTO\NoteRevisionSurplusPending;

final class CreateNoteRevisionSurplusRefundDueResultFactory
{
    public function success(
        NoteRevisionSurplusDisposition $disposition,
        ?NoteRevisionSurplusPending $after,
    ): CreateNoteRevisionSurplusRefundDueResult {
        return CreateNoteRevisionSurplusRefundDueResult::success([
            'disposition_id' => $disposition->id,
            'note_revision_settlement_id' => $disposition->noteRevisionSettlementId,
            'note_root_id' => $disposition->noteRootId,
            'note_revision_id' => $disposition->noteRevisionId,
            'disposition_type' => $disposition->dispositionType,
            'amount_rupiah' => $disposition->amountRupiah,
            'before_pending_rupiah' => $disposition->beforePendingRupiah,
            'after_pending_rupiah' => $disposition->afterPendingRupiah,
            'unresolved_pending_rupiah' => $after?->unresolvedPendingRupiah,
            'status' => $disposition->status,
            'audit_event_id' => $disposition->auditEventId,
        ]);
    }
}
