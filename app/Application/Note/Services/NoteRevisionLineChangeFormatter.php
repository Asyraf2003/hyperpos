<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Core\Note\Revision\NoteRevisionLineSnapshot;

final class NoteRevisionLineChangeFormatter
{
    public function __construct(
        private readonly NoteRevisionLineSnapshotLabelResolver $labels,
    ) {
    }

    public function addedText(int $lineNo, NoteRevisionLineSnapshot $line): string
    {
        return 'Line ' . $lineNo . ' ditambahkan: ' . $this->labels->resolve($line);
    }

    public function changedText(int $lineNo, NoteRevisionLineSnapshot $before, NoteRevisionLineSnapshot $after): string
    {
        return 'Line ' . $lineNo . ': ' . $this->signature($before) . ' -> ' . $this->signature($after);
    }

    public function removedText(int $lineNo, NoteRevisionLineSnapshot $line): string
    {
        return 'Line ' . $lineNo . ' dihapus: ' . $this->labels->resolve($line);
    }

    public function signature(NoteRevisionLineSnapshot $line): string
    {
        return sprintf(
            '%s [%s • %s]',
            $this->labels->resolve($line),
            $line->status(),
            number_format($line->subtotalRupiah(), 0, ',', '.')
        );
    }
}
