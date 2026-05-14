<?php

declare(strict_types=1);

namespace App\Adapters\Out\Note;

use App\Application\Note\DTO\NoteRevisionSettlement;
use App\Application\Note\DTO\NoteRevisionSurplusDisposition;
use App\Application\Note\DTO\NoteRevisionSurplusPending;
use Illuminate\Support\Facades\DB;

final class DatabaseNoteRevisionSurplusPendingQuery
{
    public function findBySettlementId(
        string $settlementId,
        bool $lockForUpdate,
    ): ?NoteRevisionSurplusPending {
        $settlementId = trim($settlementId);

        if ($settlementId === '') {
            return null;
        }

        $settlementQuery = DB::table('note_revision_settlements')
            ->where('id', $settlementId)
            ->where('settlement_status', NoteRevisionSettlement::STATUS_OVERPAID_PENDING);

        if ($lockForUpdate) {
            $settlementQuery->lockForUpdate();
        }

        $settlement = $settlementQuery->first();

        if ($settlement === null) {
            return null;
        }

        $activeDispositionRupiah = (int) DB::table('note_revision_surplus_dispositions')
            ->where('note_revision_settlement_id', $settlementId)
            ->where('status', NoteRevisionSurplusDisposition::STATUS_ACTIVE)
            ->sum('amount_rupiah');

        return NoteRevisionSurplusPending::create(
            (string) $settlement->id,
            (string) $settlement->note_root_id,
            (string) $settlement->note_revision_id,
            (int) $settlement->surplus_rupiah,
            $activeDispositionRupiah,
        );
    }
}
