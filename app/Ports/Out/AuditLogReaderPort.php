<?php

declare(strict_types=1);

namespace App\Ports\Out;

interface AuditLogReaderPort
{
    /**
     * @return list<array{event:string,context:array<string,mixed>,created_at:string}>
     */
    public function findLatestNoteCorrections(string $noteId, int $limit = 10): array;
}
