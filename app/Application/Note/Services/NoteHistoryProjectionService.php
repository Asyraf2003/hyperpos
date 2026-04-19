<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Core\Shared\Exceptions\DomainException;
use App\Ports\Out\ClockPort;
use App\Ports\Out\Note\NoteHistoryProjectionSourceReaderPort;
use App\Ports\Out\Note\NoteHistoryProjectionWriterPort;

final class NoteHistoryProjectionService
{
    public function __construct(
        private readonly NoteHistoryProjectionSourceReaderPort $source,
        private readonly NoteHistoryProjectionWriterPort $writer,
        private readonly ClockPort $clock,
    ) {
    }

    public function syncNote(string $noteId): void
    {
        $normalizedNoteId = trim($noteId);

        if ($normalizedNoteId === '') {
            throw new DomainException('Note id projection wajib diisi.');
        }

        $sourceRow = $this->source->findByNoteId($normalizedNoteId);

        if ($sourceRow === null) {
            throw new DomainException('Source projection note tidak ditemukan.');
        }

        $netPaidRupiah = max($sourceRow['allocated_rupiah'] - $sourceRow['refunded_rupiah'], 0);
        $outstandingRupiah = max($sourceRow['total_rupiah'] - $netPaidRupiah, 0);

        $this->writer->upsert([
            ...$sourceRow,
            'customer_name_normalized' => $this->normalizeForSearch($sourceRow['customer_name']),
            'net_paid_rupiah' => $netPaidRupiah,
            'outstanding_rupiah' => $outstandingRupiah,
            'has_open_lines' => $sourceRow['line_open_count'] > 0,
            'has_close_lines' => $sourceRow['line_close_count'] > 0,
            'has_refund_lines' => $sourceRow['line_refund_count'] > 0,
            'projected_at' => $this->clock->now()->format('Y-m-d H:i:s'),
        ]);
    }

    private function normalizeForSearch(string $value): string
    {
        $normalized = preg_replace('/\s+/', ' ', trim($value)) ?? trim($value);

        return mb_strtolower($normalized, 'UTF-8');
    }
}
