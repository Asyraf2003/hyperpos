<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Application\Shared\DTO\Result;
use App\Ports\Out\ClockPort;
use App\Ports\Out\Note\NoteReaderPort;
use App\Ports\Out\Note\NoteWriterPort;

final class FinalizeRefundedNoteFromActiveRows
{
    public function __construct(
        private readonly NoteReaderPort $notes,
        private readonly NoteWriterPort $noteWriter,
        private readonly NoteCorrectionSnapshotBuilder $snapshots,
        private readonly PersistNoteMutationTimeline $timeline,
        private readonly ClockPort $clock,
    ) {
    }

    public function execute(string $noteId, string $actorId, string $actorRole, string $reason): Result
    {
        $note = $this->notes->getById(trim($noteId));

        if ($note === null) {
            return Result::failure('Nota tidak ditemukan.', ['refund' => ['REFUND_INVALID_TARGET']]);
        }

        if ($note->totalRupiah()->amount() > 0 || $note->isRefunded()) {
            return Result::success([
                'note_id' => $noteId,
                'note_state' => $note->noteState(),
                'finalized' => false,
            ]);
        }

        $before = $this->snapshots->build($note);
        $occurredAt = $this->clock->now();

        if ($note->isOpen()) {
            $note->close(trim($actorId), $occurredAt);
        }

        if ($note->isClosed()) {
            $note->refund(trim($actorId), $occurredAt);
        }

        $this->noteWriter->updateOperationalState($note);

        $this->timeline->record(
            $note->id(),
            'note_refunded_after_selected_rows_refund',
            trim($actorId),
            trim($actorRole),
            trim($reason),
            $occurredAt,
            $before,
            $this->snapshots->build($note),
            null,
            null,
            ['active_total_rupiah' => $note->totalRupiah()->amount()],
        );

        return Result::success([
            'note_id' => $note->id(),
            'note_state' => $note->noteState(),
            'finalized' => true,
        ]);
    }
}
