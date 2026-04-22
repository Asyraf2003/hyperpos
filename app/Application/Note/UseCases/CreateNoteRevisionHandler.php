<?php

declare(strict_types=1);

namespace App\Application\Note\UseCases;

use App\Application\Note\Services\NoteCurrentRevisionResolver;
use App\Application\Note\Services\NoteRevisionBootstrapFactory;
use App\Core\Shared\Exceptions\DomainException;
use App\Ports\Out\AuditLogPort;
use App\Ports\Out\ClockPort;
use App\Ports\Out\Note\NoteReaderPort;
use App\Ports\Out\Note\NoteRevisionWriterPort;
use App\Ports\Out\TransactionManagerPort;
use Throwable;

final class CreateNoteRevisionHandler
{
    public function __construct(
        private readonly NoteReaderPort $notes,
        private readonly NoteCurrentRevisionResolver $currentRevision,
        private readonly NoteRevisionWriterPort $revisionWriter,
        private readonly NoteRevisionBootstrapFactory $factory,
        private readonly CreateNoteRevisionPayloadNoteBuilder $notesFromPayload,
        private readonly CreateNoteRevisionAuditPayloadBuilder $auditPayloads,
        private readonly ClockPort $clock,
        private readonly TransactionManagerPort $transactions,
        private readonly AuditLogPort $audit,
    ) {
    }

    /**
     * @param array{
     *   note: array<string, mixed>,
     *   items: list<array<string, mixed>>,
     *   reason: string
     * } $payload
     */
    public function handle(string $noteRootId, array $payload, ?string $actorId = null): CreateNoteRevisionResult
    {
        $started = false;

        try {
            $this->transactions->begin();
            $started = true;

            $root = $this->notes->getById(trim($noteRootId));

            if ($root === null) {
                return CreateNoteRevisionResult::failure('Root note tidak ditemukan.');
            }

            $current = $this->currentRevision->resolveOrFail($root->id());
            $nextRevisionNumber = $this->currentRevision->nextRevisionNumber($root->id());
            $revisionId = sprintf('%s-r%03d', $root->id(), $nextRevisionNumber);
            $reason = (string) ($payload['reason'] ?? '');

            $revision = $this->factory->createNextRevision(
                $revisionId,
                $current->id(),
                $nextRevisionNumber,
                $this->notesFromPayload->build($root->id(), $payload),
                $actorId,
                $this->clock->now(),
                $reason,
            );

            $this->revisionWriter->create($revision);
            $this->revisionWriter->setCurrentRevision(
                $root->id(),
                $revision->id(),
                $revision->revisionNumber(),
            );

            $this->audit->record(
                'note_revision_created',
                $this->auditPayloads->build($root->id(), $current->id(), $actorId, $reason, $revision),
            );

            $this->transactions->commit();

            return CreateNoteRevisionResult::success([
                'note_root_id' => $root->id(),
                'revision_id' => $revision->id(),
                'revision_number' => $revision->revisionNumber(),
            ], 'Revisi nota berhasil disimpan.');
        } catch (DomainException $e) {
            if ($started) {
                $this->transactions->rollBack();
            }

            return CreateNoteRevisionResult::failure($e->getMessage());
        } catch (Throwable $e) {
            if ($started) {
                $this->transactions->rollBack();
            }

            throw $e;
        }
    }
}
