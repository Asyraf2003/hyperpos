<?php

declare(strict_types=1);

namespace App\Ports\Out\Note;

use App\Application\Note\DTO\NoteRevisionSettlement;

interface NoteRevisionSettlementWriterPort
{
    public function create(NoteRevisionSettlement $settlement): void;
}
