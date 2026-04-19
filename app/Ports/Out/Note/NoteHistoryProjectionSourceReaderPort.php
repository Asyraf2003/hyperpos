<?php

declare(strict_types=1);

namespace App\Ports\Out\Note;

interface NoteHistoryProjectionSourceReaderPort
{
    /**
     * @return array{
     *   note_id: string,
     *   transaction_date: string,
     *   note_state: string,
     *   customer_name: string,
     *   customer_phone: ?string,
     *   total_rupiah: int,
     *   allocated_rupiah: int,
     *   refunded_rupiah: int,
     *   line_open_count: int,
     *   line_close_count: int,
     *   line_refund_count: int
     * }|null
     */
    public function findByNoteId(string $noteId): ?array;
}
