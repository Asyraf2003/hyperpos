<?php

declare(strict_types=1);

namespace App\Adapters\Out\Note\Concerns;

use App\Core\Note\Revision\NoteRevision;
use Illuminate\Support\Facades\DB;

trait WritesNoteRevisionRecords
{
    public function create(NoteRevision $revision): void
    {
        DB::table('note_revisions')->insert($this->rows->toInsertRow($revision));

        $createdAt = $revision->createdAt()->format('Y-m-d H:i:s');
        $lineRows = [];

        foreach ($revision->lines() as $line) {
            $lineRows[] = $this->lineRows->toInsertRow($line, $createdAt);
        }

        if ($lineRows !== []) {
            DB::table('note_revision_lines')->insert($lineRows);
        }
    }

    public function setCurrentRevision(string $noteRootId, string $revisionId, int $revisionNumber): void
    {
        DB::table('notes')
            ->where('id', trim($noteRootId))
            ->update([
                'current_revision_id' => trim($revisionId),
                'latest_revision_number' => $revisionNumber,
            ]);
    }
}
