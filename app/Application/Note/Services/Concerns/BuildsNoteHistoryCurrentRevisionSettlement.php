<?php

declare(strict_types=1);

namespace App\Application\Note\Services\Concerns;

trait BuildsNoteHistoryCurrentRevisionSettlement
{
    /**
     * @return array{
     *   net_paid_rupiah:int,
     *   outstanding_rupiah:int,
     *   line_open_count:int,
     *   line_close_count:int,
     *   line_refund_count:int
     * }|null
     */
    private function currentRevisionSettlement(string $noteId): ?array
    {
        if (! $this->currentRevision->hasRevision($noteId)) {
            return null;
        }

        $revision = $this->currentRevision->resolveOrFail($noteId);
        $lines = $revision->lines();

        $settlements = $this->currentRevisionSettlements->build(
            $revision->noteRootId(),
            $lines,
        );

        $rows = [];
        $netPaid = 0;
        $outstanding = 0;

        foreach ($lines as $line) {
            $key = $line->workItemRootId() ?? $line->id();
            $settlement = $settlements[$key] ?? [];
            $refunded = (int) ($settlement['refunded_rupiah'] ?? 0);
            $lineOutstanding = (int) (
                $settlement['outstanding_rupiah']
                ?? $line->subtotalRupiah()
            );

            $rows[] = [
                'line_status' => $this->workItemStatuses->resolve(
                    $lineOutstanding,
                    $refunded,
                ),
            ];

            $netPaid += (int) ($settlement['net_paid_rupiah'] ?? 0);
            $outstanding += $lineOutstanding;
        }

        $summary = $this->lineSummary->build($rows);

        return [
            'net_paid_rupiah' => $netPaid,
            'outstanding_rupiah' => $outstanding,
            'line_open_count' => (int) $summary['open_count'],
            'line_close_count' => (int) $summary['close_count'],
            'line_refund_count' => (int) $summary['refund_count'],
        ];
    }
}
