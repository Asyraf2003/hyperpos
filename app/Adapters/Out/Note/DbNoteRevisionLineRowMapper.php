<?php

declare(strict_types=1);

namespace App\Adapters\Out\Note;

use App\Core\Note\Revision\NoteRevisionLineSnapshot;

final class DbNoteRevisionLineRowMapper
{
    public function __construct(
        private readonly DbNoteRevisionPayloadCodec $payloads,
    ) {
    }

    public function map(object $row): NoteRevisionLineSnapshot
    {
        return NoteRevisionLineSnapshot::create(
            (string) $row->id,
            (string) $row->note_revision_id,
            isset($row->work_item_root_id) ? (string) $row->work_item_root_id : null,
            (int) $row->line_no,
            (string) $row->transaction_type,
            (string) $row->status,
            (int) $row->subtotal_rupiah,
            isset($row->service_label) ? (string) $row->service_label : null,
            isset($row->service_price_rupiah) ? (int) $row->service_price_rupiah : null,
            $this->payloads->decode($row->payload ?? null),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toInsertRow(NoteRevisionLineSnapshot $line, string $createdAt): array
    {
        return [
            'id' => $line->id(),
            'note_revision_id' => $line->noteRevisionId(),
            'work_item_root_id' => $line->workItemRootId(),
            'line_no' => $line->lineNo(),
            'transaction_type' => $line->transactionType(),
            'status' => $line->status(),
            'service_label' => $line->serviceLabel(),
            'service_price_rupiah' => $line->servicePriceRupiah(),
            'subtotal_rupiah' => $line->subtotalRupiah(),
            'payload' => $this->payloads->encode($line->payload()),
            'created_at' => $createdAt,
            'updated_at' => null,
        ];
    }
}
