<?php

declare(strict_types=1);

namespace App\Adapters\Out\Note;

use App\Application\Note\DTO\NoteRevisionSettlement;
use App\Application\Note\DTO\NoteRevisionSurplusDisposition;
use App\Application\Note\DTO\NoteRevisionSurplusPending;
use App\Ports\Out\Note\NoteRevisionSurplusDispositionReaderPort;
use App\Ports\Out\Note\NoteRevisionSurplusDispositionWriterPort;
use Illuminate\Support\Facades\DB;

final class DatabaseNoteRevisionSurplusDispositionAdapter implements
    NoteRevisionSurplusDispositionReaderPort,
    NoteRevisionSurplusDispositionWriterPort
{
    public function create(NoteRevisionSurplusDisposition $disposition): void
    {
        DB::table('note_revision_surplus_dispositions')->insert([
            'id' => $disposition->id,
            'note_revision_settlement_id' => $disposition->noteRevisionSettlementId,
            'note_root_id' => $disposition->noteRootId,
            'note_revision_id' => $disposition->noteRevisionId,
            'disposition_type' => $disposition->dispositionType,
            'amount_rupiah' => $disposition->amountRupiah,
            'before_pending_rupiah' => $disposition->beforePendingRupiah,
            'after_pending_rupiah' => $disposition->afterPendingRupiah,
            'status' => $disposition->status,
            'occurred_at' => $disposition->occurredAt->format('Y-m-d H:i:s'),
            'created_at' => $disposition->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => null,
            'audit_event_id' => $disposition->auditEventId,
        ]);
    }

    public function findPendingBySettlementId(string $settlementId): ?NoteRevisionSurplusPending
    {
        return (new DatabaseNoteRevisionSurplusPendingQuery())->findBySettlementId($settlementId, false);
    }

    public function findPendingBySettlementIdForUpdate(string $settlementId): ?NoteRevisionSurplusPending
    {
        return (new DatabaseNoteRevisionSurplusPendingQuery())->findBySettlementId($settlementId, true);
    }

    /** @return list<NoteRevisionSurplusPending> */
    public function findPendingByNoteRootId(string $noteRootId): array
    {
        $noteRootId = trim($noteRootId);

        if ($noteRootId === '') {
            return [];
        }

        $settlements = DB::table('note_revision_settlements')
            ->where('note_root_id', $noteRootId)
            ->where('settlement_status', NoteRevisionSettlement::STATUS_OVERPAID_PENDING)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        $pending = [];

        foreach ($settlements as $settlement) {
            $item = $this->findPendingBySettlementId((string) $settlement->id);

            if ($item !== null && $item->unresolvedPendingRupiah > 0) {
                $pending[] = $item;
            }
        }

        return $pending;
    }
}
