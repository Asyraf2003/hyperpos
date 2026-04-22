<?php

declare(strict_types=1);

namespace App\Adapters\Out\Note;

use App\Adapters\Out\Note\Concerns\QueriesNoteRevisionRecords;
use App\Adapters\Out\Note\Concerns\WritesNoteRevisionRecords;
use App\Ports\Out\Note\NoteRevisionReaderPort;
use App\Ports\Out\Note\NoteRevisionWriterPort;

final class DbNoteRevisionRepository implements NoteRevisionReaderPort, NoteRevisionWriterPort
{
    use QueriesNoteRevisionRecords;
    use WritesNoteRevisionRecords;

    public function __construct(
        private readonly DbNoteRevisionRowMapper $rows,
        private readonly DbNoteRevisionLineRowMapper $lineRows,
    ) {
    }
}
