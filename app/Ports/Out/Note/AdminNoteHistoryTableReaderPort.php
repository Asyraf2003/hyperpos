<?php

declare(strict_types=1);

namespace App\Ports\Out\Note;

interface AdminNoteHistoryTableReaderPort
{
    /**
     * @param array<string, mixed> $filters
     * @return array{
     *   filters: array<string, mixed>,
     *   items: list<array<string, mixed>>,
     *   pagination: array<string, int>,
     *   summary: array{label: string}
     * }
     */
    public function get(array $filters): array;
}
