<?php

declare(strict_types=1);

namespace Tests\Feature\Note;

use App\Ports\Out\Note\DueNoteReminderReaderPort;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class DatabaseDueNoteReminderReaderFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_h5_and_overdue_open_outstanding_notes_only(): void
    {
        $this->seedReminderCandidate(
            noteId: 'note-h5',
            transactionDate: '2026-04-23',
            dueDate: '2026-05-23',
            customerName: 'Budi H5',
            noteState: 'open',
            outstandingRupiah: 10000,
        );

        $this->seedReminderCandidate(
            noteId: 'note-h6',
            transactionDate: '2026-04-24',
            dueDate: '2026-05-24',
            customerName: 'Andi H6',
            noteState: 'open',
            outstandingRupiah: 20000,
        );

        $this->seedReminderCandidate(
            noteId: 'note-overdue',
            transactionDate: '2026-04-10',
            dueDate: '2026-05-10',
            customerName: 'Cici Overdue',
            noteState: 'open',
            outstandingRupiah: 30000,
        );

        $this->seedReminderCandidate(
            noteId: 'note-paid',
            transactionDate: '2026-04-20',
            dueDate: '2026-05-20',
            customerName: 'Dedi Paid',
            noteState: 'open',
            outstandingRupiah: 0,
        );

        $this->seedReminderCandidate(
            noteId: 'note-closed',
            transactionDate: '2026-04-20',
            dueDate: '2026-05-20',
            customerName: 'Eka Closed',
            noteState: 'closed',
            outstandingRupiah: 40000,
        );

        $rows = app(DueNoteReminderReaderPort::class)
            ->findDueReminders('2026-05-18');

        self::assertSame(['note-overdue', 'note-h5'], array_map(
            static fn ($row): string => $row->noteId,
            $rows,
        ));

        self::assertSame(8, $rows[0]->daysOverdue);
        self::assertSame(0, $rows[1]->daysOverdue);
        self::assertSame(40000, array_sum(array_map(
            static fn ($row): int => $row->outstandingRupiah,
            $rows,
        )));
    }

    private function seedReminderCandidate(
        string $noteId,
        string $transactionDate,
        string $dueDate,
        string $customerName,
        string $noteState,
        int $outstandingRupiah,
    ): void {
        $totalRupiah = $outstandingRupiah > 0 ? $outstandingRupiah : 50000;
        $netPaidRupiah = max($totalRupiah - $outstandingRupiah, 0);

        DB::table('notes')->insert([
            'id' => $noteId,
            'customer_name' => $customerName,
            'customer_phone' => '081234567',
            'transaction_date' => $transactionDate,
            'due_date' => $dueDate,
            'total_rupiah' => $totalRupiah,
            'note_state' => $noteState,
        ]);

        DB::table('note_history_projection')->insert([
            'note_id' => $noteId,
            'transaction_date' => $transactionDate,
            'note_state' => $noteState,
            'customer_name' => $customerName,
            'customer_name_normalized' => mb_strtolower($customerName, 'UTF-8'),
            'customer_phone' => '081234567',
            'total_rupiah' => $totalRupiah,
            'allocated_rupiah' => $netPaidRupiah,
            'refunded_rupiah' => 0,
            'net_paid_rupiah' => $netPaidRupiah,
            'outstanding_rupiah' => $outstandingRupiah,
            'line_open_count' => 1,
            'line_close_count' => 0,
            'line_refund_count' => 0,
            'has_open_lines' => true,
            'has_close_lines' => false,
            'has_refund_lines' => false,
            'projected_at' => '2026-05-18 08:00:00',
        ]);
    }
}
