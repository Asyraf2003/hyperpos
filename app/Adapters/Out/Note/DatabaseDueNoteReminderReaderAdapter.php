<?php

declare(strict_types=1);

namespace App\Adapters\Out\Note;

use App\Application\Note\DTO\DueNoteReminderRow;
use App\Ports\Out\Note\DueNoteReminderReaderPort;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class DatabaseDueNoteReminderReaderAdapter implements DueNoteReminderReaderPort
{
    public function findDueReminders(string $today, int $limit = 100): array
    {
        $todayDate = $this->parseDate($today);
        $maxDueDate = $todayDate->modify('+5 days')->format('Y-m-d');

        $rows = DB::table('notes')
            ->join('note_history_projection', 'note_history_projection.note_id', '=', 'notes.id')
            ->whereNotNull('notes.due_date')
            ->whereDate('notes.due_date', '<=', $maxDueDate)
            ->where('note_history_projection.note_state', 'open')
            ->where('note_history_projection.outstanding_rupiah', '>', 0)
            ->orderBy('notes.due_date')
            ->orderBy('note_history_projection.customer_name')
            ->limit(max(1, $limit))
            ->get([
                'notes.id as note_id',
                'note_history_projection.customer_name',
                'note_history_projection.customer_phone',
                'notes.transaction_date',
                'notes.due_date',
                'note_history_projection.outstanding_rupiah',
            ])
            ->all();

        return array_map(
            fn (object $row): DueNoteReminderRow => $this->mapRow($row, $todayDate),
            $rows,
        );
    }

    private function mapRow(object $row, DateTimeImmutable $today): DueNoteReminderRow
    {
        $dueDate = $this->parseDate((string) $row->due_date);

        return new DueNoteReminderRow(
            noteId: (string) $row->note_id,
            customerName: (string) $row->customer_name,
            customerPhone: $row->customer_phone === null ? null : (string) $row->customer_phone,
            transactionDate: (string) $row->transaction_date,
            dueDate: $dueDate->format('Y-m-d'),
            outstandingRupiah: (int) $row->outstanding_rupiah,
            daysOverdue: $this->daysOverdue($today, $dueDate),
        );
    }

    private function parseDate(string $value): DateTimeImmutable
    {
        $normalized = trim($value);
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $normalized);

        if ($parsed === false || $parsed->format('Y-m-d') !== $normalized) {
            throw new InvalidArgumentException('Tanggal due reminder wajib valid dengan format Y-m-d.');
        }

        return $parsed;
    }

    private function daysOverdue(DateTimeImmutable $today, DateTimeImmutable $dueDate): int
    {
        if ($today <= $dueDate) {
            return 0;
        }

        return (int) $dueDate->diff($today)->days;
    }
}
