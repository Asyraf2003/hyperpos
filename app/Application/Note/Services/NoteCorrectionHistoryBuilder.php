<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Ports\Out\Note\NoteCorrectionHistoryReaderPort;

final class NoteCorrectionHistoryBuilder
{
    public function __construct(
        private readonly NoteCorrectionHistoryReaderPort $history,
    ) {
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function build(string $noteId): array
    {
        return $this->history->findLatestNoteCorrections($noteId);
    }
}
