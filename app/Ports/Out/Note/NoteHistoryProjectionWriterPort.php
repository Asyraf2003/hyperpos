<?php

declare(strict_types=1);

namespace App\Ports\Out\Note;

interface NoteHistoryProjectionWriterPort
{
    /**
     * @param array{
     *   note_id: string,
     *   transaction_date: string,
     *   note_state: string,
     *   customer_name: string,
     *   customer_name_normalized: string,
     *   customer_phone: ?string,
     *   total_rupiah: int,
     *   allocated_rupiah: int,
     *   refunded_rupiah: int,
     *   net_paid_rupiah: int,
     *   outstanding_rupiah: int,
     *   line_open_count: int,
     *   line_close_count: int,
     *   line_refund_count: int,
     *   has_open_lines: bool,
     *   has_close_lines: bool,
     *   has_refund_lines: bool,
     *   projected_at: string
     * } $row
     */
    public function upsert(array $row): void;
}
