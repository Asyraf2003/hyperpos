<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Ports\Out\Note\NoteReaderPort;

final readonly class NoteRowsStartingLineNoResolver
{
    public function __construct(private NoteReaderPort $notes)
    {
    }

    public function resolve(string $noteId): ?int
    {
        $note = $this->notes->getById(trim($noteId));

        if ($note === null) {
            return null;
        }

        $maxLineNo = 0;

        foreach ($note->workItems() as $item) {
            $maxLineNo = max($maxLineNo, $item->lineNo());
        }

        return $maxLineNo + 1;
    }
}
