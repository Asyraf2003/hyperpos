<?php

declare(strict_types=1);

namespace App\Adapters\Out\Note;

use App\Core\Note\Revision\NoteRevision;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;

final class DbNoteRevisionRowMapper
{
    public function __construct(
        private readonly DbNoteRevisionLineRowMapper $lines,
    ) {
    }

    public function map(object $row): NoteRevision
    {
        $mappedLines = DB::table('note_revision_lines')
            ->where('note_revision_id', (string) $row->id)
            ->orderBy('line_no')
            ->get()
            ->map(fn (object $line) => $this->lines->map($line))
            ->all();

        return NoteRevision::create(
            (string) $row->id,
            (string) $row->note_root_id,
            (int) $row->revision_number,
            isset($row->parent_revision_id) ? (string) $row->parent_revision_id : null,
            isset($row->created_by_actor_id) ? (string) $row->created_by_actor_id : null,
            isset($row->reason) ? (string) $row->reason : null,
            (string) $row->customer_name,
            isset($row->customer_phone) ? (string) $row->customer_phone : null,
            new DateTimeImmutable((string) $row->transaction_date),
            (int) $row->grand_total_rupiah,
            $mappedLines,
            new DateTimeImmutable((string) $row->created_at),
            property_exists($row, 'subtotal_before_note_tax_rupiah') ? (int) $row->subtotal_before_note_tax_rupiah : null,
            property_exists($row, 'note_tax_input') && $row->note_tax_input !== null ? (string) $row->note_tax_input : null,
            property_exists($row, 'note_tax_mode') && $row->note_tax_mode !== null ? (string) $row->note_tax_mode : NoteRevision::TAX_MODE_NONE,
            property_exists($row, 'note_tax_rate_basis_points') && $row->note_tax_rate_basis_points !== null ? (int) $row->note_tax_rate_basis_points : null,
            property_exists($row, 'note_tax_amount_rupiah') ? (int) $row->note_tax_amount_rupiah : 0,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toInsertRow(NoteRevision $revision): array
    {
        return [
            'id' => $revision->id(),
            'note_root_id' => $revision->noteRootId(),
            'revision_number' => $revision->revisionNumber(),
            'parent_revision_id' => $revision->parentRevisionId(),
            'created_by_actor_id' => $revision->createdByActorId(),
            'reason' => $revision->reason(),
            'customer_name' => $revision->customerName(),
            'customer_phone' => $revision->customerPhone(),
            'transaction_date' => $revision->transactionDate()->format('Y-m-d'),
            'grand_total_rupiah' => $revision->grandTotalRupiah(),
            'subtotal_before_note_tax_rupiah' => $revision->subtotalBeforeNoteTaxRupiah(),
            'note_tax_input' => $revision->noteTaxInput(),
            'note_tax_mode' => $revision->noteTaxMode(),
            'note_tax_rate_basis_points' => $revision->noteTaxRateBasisPoints(),
            'note_tax_amount_rupiah' => $revision->noteTaxAmountRupiah(),
            'line_count' => $revision->lineCount(),
            'created_at' => $revision->createdAt()->format('Y-m-d H:i:s'),
            'updated_at' => null,
        ];
    }
}
