<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Core\Note\Note\Note;
use App\Ports\Out\ClockPort;
use App\Ports\Out\Note\NoteRevisionWriterPort;

final class CreateTransactionWorkspaceInitialRevisionBootstrapper
{
    public function __construct(
        private readonly NoteRevisionBootstrapFactory $factory,
        private readonly NoteRevisionWriterPort $revisions,
        private readonly ClockPort $clock,
    ) {
    }

    public function bootstrap(Note $note, ?string $actorId): void
    {
        $revision = $this->factory->createInitialRevision(
            sprintf('%s-r001', $note->id()),
            $note,
            $actorId,
            $this->clock->now(),
            'Bootstrap initial revision from transaction workspace create',
        );

        $this->revisions->create($revision);
        $this->revisions->setCurrentRevision(
            $note->id(),
            $revision->id(),
            $revision->revisionNumber(),
        );
    }
}
