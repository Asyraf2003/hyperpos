<?php

declare(strict_types=1);

namespace App\Adapters\Out\Note;

use App\Ports\Out\Note\NoteHistoryProjectionWriterPort;
use Illuminate\Support\Facades\DB;

final class DatabaseNoteHistoryProjectionWriterAdapter implements NoteHistoryProjectionWriterPort
{
    public function upsert(array $row): void
    {
        DB::table('note_history_projection')->updateOrInsert(
            ['note_id' => $row['note_id']],
            [
                'transaction_date' => $row['transaction_date'],
                'note_state' => $row['note_state'],
                'customer_name' => $row['customer_name'],
                'customer_name_normalized' => $row['customer_name_normalized'],
                'customer_phone' => $row['customer_phone'],
                'total_rupiah' => $row['total_rupiah'],
                'allocated_rupiah' => $row['allocated_rupiah'],
                'refunded_rupiah' => $row['refunded_rupiah'],
                'net_paid_rupiah' => $row['net_paid_rupiah'],
                'outstanding_rupiah' => $row['outstanding_rupiah'],
                'line_open_count' => $row['line_open_count'],
                'line_close_count' => $row['line_close_count'],
                'line_refund_count' => $row['line_refund_count'],
                'has_open_lines' => $row['has_open_lines'],
                'has_close_lines' => $row['has_close_lines'],
                'has_refund_lines' => $row['has_refund_lines'],
                'projected_at' => $row['projected_at'],
            ],
        );
    }
}
