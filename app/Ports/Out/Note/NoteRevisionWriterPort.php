<?php

declare(strict_types=1);

namespace App\Ports\Out\Note;

use App\Core\Note\Revision\NoteRevision;

interface NoteRevisionWriterPort
{
    public function create(NoteRevision $revision): void;

    public function setCurrentRevision(string $noteRootId, string $revisionId, int $revisionNumber): void;
}
