<?php

declare(strict_types=1);

namespace App\Application\Note\UseCases;

use App\Application\Note\Services\NoteCorrectionSnapshotBuilder;
use App\Application\Note\Services\PersistNoteMutationTimeline;
use App\Application\Shared\DTO\Result;
use App\Core\Shared\Exceptions\DomainException;
use App\Ports\Out\AuditLogPort;
use App\Ports\Out\ClockPort;
use App\Ports\Out\Note\NoteReaderPort;
use App\Ports\Out\Note\NoteWriterPort;
use App\Ports\Out\TransactionManagerPort;
use Throwable;

final class ReopenClosedNoteHandler
{
    public function __construct(
        private readonly NoteReaderPort $notes,
        private readonly NoteWriterPort $noteWriter,
        private readonly TransactionManagerPort $transactions,
        private readonly NoteCorrectionSnapshotBuilder $snapshots,
        private readonly PersistNoteMutationTimeline $timeline,
        private readonly ClockPort $clock,
        private readonly AuditLogPort $audit,
    ) {
    }

    public function handle(string $noteId, string $reason, string $performedByActorId): Result
    {
        if (trim($reason) === '') {
            return Result::failure('Alasan reopen wajib diisi.', ['note' => ['AUDIT_REASON_REQUIRED']]);
        }

        $started = false;

        try {
            $actorId = trim($performedByActorId);

            if ($actorId === '') {
                throw new DomainException('Actor reopen wajib ada.');
            }

            $this->transactions->begin();
            $started = true;

            $note = $this->notes->getById(trim($noteId))
                ?? throw new DomainException('Note tidak ditemukan.');

            if (!$note->isClosed()) {
                throw new DomainException('Hanya note closed yang boleh dibuka kembali.');
            }

            $before = $this->snapshots->build($note);
            $occurredAt = $this->clock->now();

            $note->reopen($actorId, $occurredAt);
            $this->noteWriter->updateOperationalState($note);

            $this->timeline->record(
                $note->id(),
                'note_reopened',
                $actorId,
                'admin',
                trim($reason),
                $occurredAt,
                $before,
                $this->snapshots->build($note),
            );

            $this->audit->record('note_reopened', [
                'note_id' => $note->id(),
                'actor_id' => $actorId,
                'reason' => trim($reason),
            ]);

            $this->transactions->commit();

            return Result::success([
                'note_id' => $note->id(),
                'note_state' => $note->noteState(),
                'reopened_by_actor_id' => $note->reopenedByActorId(),
                'reopened_at' => $note->reopenedAt()?->format('Y-m-d H:i:s'),
            ], 'Note berhasil dibuka kembali.');
        } catch (DomainException $e) {
            if ($started) $this->transactions->rollBack();
            return Result::failure($e->getMessage(), ['note' => ['INVALID_NOTE_STATE']]);
        } catch (Throwable $e) {
            if ($started) $this->transactions->rollBack();
            throw $e;
        }
    }
}
