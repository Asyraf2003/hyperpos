<?php

declare(strict_types=1);

namespace App\Ports\Out\Note;

use App\Application\Note\DTO\NoteRevisionSettlement;

interface NoteRevisionSettlementReaderPort
{
    public function findByRevisionId(string $noteRevisionId): ?NoteRevisionSettlement;

    /**
     * @return list<NoteRevisionSettlement>
     */
    public function listByNoteRootId(string $noteRootId): array;
}
