<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Application\Note\Services\Concerns\BuildsNoteHistoryCurrentRevisionSettlement;
use App\Application\Note\Services\CurrentRevision\CurrentRevisionRowSettlementProjector;
use App\Core\Shared\Exceptions\DomainException;
use App\Ports\Out\ClockPort;
use App\Ports\Out\Note\NoteHistoryProjectionSourceReaderPort;
use App\Ports\Out\Note\NoteHistoryProjectionWriterPort;

final class NoteHistoryProjectionService
{
    use BuildsNoteHistoryCurrentRevisionSettlement;

    public function __construct(
        private readonly NoteHistoryProjectionSourceReaderPort $source,
        private readonly NoteHistoryProjectionWriterPort $writer,
        private readonly ClockPort $clock,
        private readonly NoteCurrentRevisionResolver $currentRevision,
        private readonly CurrentRevisionRowSettlementProjector $currentRevisionSettlements,
        private readonly NoteLineSummaryBuilder $lineSummary,
        private readonly WorkItemOperationalStatusResolver $workItemStatuses,
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

        $activeTotalRupiah = (int) $sourceRow['total_rupiah'];
        $currentSettlement = $activeTotalRupiah > 0
            ? $this->currentRevisionSettlement($normalizedNoteId)
            : [
                'net_paid_rupiah' => 0,
                'outstanding_rupiah' => 0,
                'line_open_count' => 0,
                'line_close_count' => 0,
                'line_refund_count' => $sourceRow['line_refund_count'],
            ];

        $netPaidRupiah = $currentSettlement['net_paid_rupiah']
            ?? max($sourceRow['allocated_rupiah'] - $sourceRow['refunded_rupiah'], 0);

        $outstandingRupiah = $currentSettlement['outstanding_rupiah']
            ?? max($sourceRow['total_rupiah'] - $netPaidRupiah, 0);

        $lineOpenCount = $currentSettlement['line_open_count']
            ?? $sourceRow['line_open_count'];

        $lineCloseCount = $currentSettlement['line_close_count']
            ?? $sourceRow['line_close_count'];

        $lineRefundCount = $currentSettlement['line_refund_count']
            ?? $sourceRow['line_refund_count'];

        $this->writer->upsert([
            ...$sourceRow,
            'customer_name_normalized' => $this->normalizeForSearch(
                $sourceRow['customer_name'],
            ),
            'net_paid_rupiah' => $netPaidRupiah,
            'outstanding_rupiah' => $outstandingRupiah,
            'line_open_count' => $lineOpenCount,
            'line_close_count' => $lineCloseCount,
            'line_refund_count' => $lineRefundCount,
            'has_open_lines' => $lineOpenCount > 0,
            'has_close_lines' => $lineCloseCount > 0,
            'has_refund_lines' => $lineRefundCount > 0,
            'projected_at' => $this->clock->now()->format('Y-m-d H:i:s'),
        ]);
    }

    private function normalizeForSearch(string $value): string
    {
        $normalized = preg_replace('/\s+/', ' ', trim($value)) ?? trim($value);

        return mb_strtolower($normalized, 'UTF-8');
    }
}
