<?php

declare(strict_types=1);

namespace App\Application\Note\UseCases;

use App\Core\Note\Revision\NoteRevision;
use App\Ports\Out\AuditLogPort;
use App\Ports\Out\Note\NoteRevisionWriterPort;

final class CreateNoteRevisionCommitter
{
    public function __construct(
        private readonly NoteRevisionWriterPort $revisionWriter,
        private readonly CreateNoteRevisionAuditPayloadBuilder $auditPayloads,
        private readonly AuditLogPort $audit,
    ) {
    }

    public function commit(
        string $noteRootId,
        string $parentRevisionId,
        ?string $actorId,
        string $reason,
        NoteRevision $revision,
    ): CreateNoteRevisionResult {
        $this->revisionWriter->create($revision);
        $this->revisionWriter->setCurrentRevision(
            $noteRootId,
            $revision->id(),
            $revision->revisionNumber(),
        );

        $this->audit->record(
            'note_revision_created',
            $this->auditPayloads->build($noteRootId, $parentRevisionId, $actorId, $reason, $revision),
        );

        return CreateNoteRevisionResult::success([
            'note_root_id' => $noteRootId,
            'revision_id' => $revision->id(),
            'revision_number' => $revision->revisionNumber(),
        ], 'Revisi nota berhasil disimpan.');
    }
}
