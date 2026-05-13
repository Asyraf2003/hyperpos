<?php

declare(strict_types=1);

namespace App\Adapters\Out\Note;

use App\Application\Note\DTO\NoteRevisionSettlement;
use App\Ports\Out\Note\NoteRevisionSettlementReaderPort;
use App\Ports\Out\Note\NoteRevisionSettlementWriterPort;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;

final class DatabaseNoteRevisionSettlementAdapter implements NoteRevisionSettlementReaderPort, NoteRevisionSettlementWriterPort
{
    public function create(NoteRevisionSettlement $settlement): void
    {
        DB::table('note_revision_settlements')->insert($this->toRow($settlement));
    }

    public function findByRevisionId(string $noteRevisionId): ?NoteRevisionSettlement
    {
        $row = DB::table('note_revision_settlements')
            ->where('note_revision_id', trim($noteRevisionId))
            ->first();

        return $row === null ? null : $this->map($row);
    }

    public function listByNoteRootId(string $noteRootId): array
    {
        return DB::table('note_revision_settlements')
            ->where('note_root_id', trim($noteRootId))
            ->orderBy('created_at')
            ->get()
            ->map(fn (object $row): NoteRevisionSettlement => $this->map($row))
            ->all();
    }

    private function toRow(NoteRevisionSettlement $settlement): array
    {
        return [
            'id' => $settlement->id,
            'note_revision_id' => $settlement->noteRevisionId,
            'note_root_id' => $settlement->noteRootId,
            'gross_total_rupiah' => $settlement->grossTotalRupiah,
            'carry_forward_paid_rupiah' => $settlement->carryForwardPaidRupiah,
            'carry_forward_refunded_rupiah' => $settlement->carryForwardRefundedRupiah,
            'net_paid_rupiah' => $settlement->netPaidRupiah,
            'outstanding_rupiah' => $settlement->outstandingRupiah,
            'surplus_rupiah' => $settlement->surplusRupiah,
            'settlement_status' => $settlement->settlementStatus,
            'created_at' => $settlement->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => null,
        ];
    }

    private function map(object $row): NoteRevisionSettlement
    {
        return NoteRevisionSettlement::create(
            (string) $row->id,
            (string) $row->note_revision_id,
            (string) $row->note_root_id,
            (int) $row->gross_total_rupiah,
            (int) $row->carry_forward_paid_rupiah,
            (int) $row->carry_forward_refunded_rupiah,
            (int) $row->net_paid_rupiah,
            (int) $row->outstanding_rupiah,
            (int) $row->surplus_rupiah,
            (string) $row->settlement_status,
            new DateTimeImmutable((string) $row->created_at),
        );
    }
}
