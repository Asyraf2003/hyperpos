<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Core\Note\Revision\NoteRevision;

final class NoteRevisionTimelineSummaryBuilder
{
    public function __construct(
        private readonly NoteRevisionLineChangeFormatter $formatter,
    ) {
    }

    public function build(NoteRevision $revision, ?NoteRevision $parent): array
    {
        if ($parent === null) {
            return ['Ringkasan awal nota.'];
        }

        $current = $this->indexLines($revision);
        $previous = $this->indexLines($parent);
        $changes = [];

        foreach ($current as $lineNo => $line) {
            if (!isset($previous[$lineNo])) {
                $changes[] = $this->formatter->addedText($lineNo, $line);
                continue;
            }

            if ($this->formatter->signature($previous[$lineNo]) !== $this->formatter->signature($line)) {
                $changes[] = $this->formatter->changedText($lineNo, $previous[$lineNo], $line);
            }
        }

        foreach ($previous as $lineNo => $line) {
            if (!isset($current[$lineNo])) {
                $changes[] = $this->formatter->removedText($lineNo, $line);
            }
        }

        return $changes === [] ? ['Tidak ada perubahan line yang terdeteksi.'] : array_slice($changes, 0, 3);
    }

    private function indexLines(NoteRevision $revision): array
    {
        $indexed = [];

        foreach ($revision->lines() as $line) {
            $indexed[$line->lineNo()] = $line;
        }

        ksort($indexed);

        return $indexed;
    }
}
