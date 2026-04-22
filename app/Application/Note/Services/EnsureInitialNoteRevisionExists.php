<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Core\Shared\Exceptions\DomainException;
use App\Ports\Out\ClockPort;
use App\Ports\Out\Note\NoteReaderPort;
use App\Ports\Out\Note\NoteRevisionReaderPort;
use App\Ports\Out\Note\NoteRevisionWriterPort;

final class EnsureInitialNoteRevisionExists
{
    public function __construct(
        private readonly NoteReaderPort $notes,
        private readonly NoteRevisionReaderPort $revisions,
        private readonly NoteRevisionWriterPort $revisionWriter,
        private readonly NoteRevisionBootstrapFactory $factory,
        private readonly ClockPort $clock,
    ) {
    }

    public function handle(string $noteRootId, string $revisionId, ?string $actorId = null): void
    {
        $normalized = trim($noteRootId);

        if ($normalized === '') {
            throw new DomainException('Note root id wajib diisi.');
        }

        if ($this->revisions->existsForRootId($normalized)) {
            return;
        }

        $note = $this->notes->getById($normalized);

        if ($note === null) {
            throw new DomainException('Note root tidak ditemukan untuk bootstrap revision awal.');
        }

        $revision = $this->factory->createInitialRevision(
            trim($revisionId),
            $note,
            $actorId,
            $this->clock->now(),
        );

        $this->revisionWriter->create($revision);
        $this->revisionWriter->setCurrentRevision(
            $note->id(),
            $revision->id(),
            $revision->revisionNumber(),
        );
    }
}
