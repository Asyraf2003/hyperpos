<?php

declare(strict_types=1);

namespace App\Ports\Out\Note;

use App\Core\Note\Revision\NoteRevision;

interface NoteRevisionReaderPort
{
    public function findById(string $revisionId): ?NoteRevision;

    public function findCurrentByRootId(string $noteRootId): ?NoteRevision;

    public function nextRevisionNumber(string $noteRootId): int;

    /**
     * @return list<NoteRevision>
     */
    public function findTimelineByRootId(string $noteRootId, int $limit = 50): array;

    public function existsForRootId(string $noteRootId): bool;
}
