<?php

declare(strict_types=1);

namespace App\Application\Note\Services\RevisionWorkspace;

use App\Core\Note\Revision\NoteRevision;
use App\Core\Note\WorkItem\WorkItem;

final class RevisionSnapshotStoreStockLineTrustInventory
{
    public function __construct(
        private readonly RevisionSnapshotStoreStockLineKeyer $keyer,
    ) {
    }

    /**
     * @param list<WorkItem> $workItems
     * @return array<string, int>
     */
    public function countAvailable(?NoteRevision $revision, array $workItems): array
    {
        return $this->addWorkItemCounts(
            $this->snapshotCounts($revision),
            $workItems,
        );
    }

    /** @return array<string, int> */
    private function snapshotCounts(?NoteRevision $revision): array
    {
        if ($revision === null) {
            return [];
        }

        $counts = [];

        foreach ($revision->lines() as $line) {
            $payload = $line->payload();
            $storeLines = $payload['store_stock_lines'] ?? [];

            if (! is_array($storeLines)) {
                continue;
            }

            foreach ($storeLines as $storeLine) {
                if (! is_array($storeLine)) {
                    continue;
                }

                $key = $this->keyer->fromParts(
                    (string) ($storeLine['product_id'] ?? ''),
                    (int) ($storeLine['qty'] ?? 0),
                    (int) ($storeLine['line_total_rupiah'] ?? 0),
                );

                $counts[$key] = ($counts[$key] ?? 0) + 1;
            }
        }

        return $counts;
    }

    /**
     * @param array<string, int> $counts
     * @param list<WorkItem> $workItems
     * @return array<string, int>
     */
    private function addWorkItemCounts(array $counts, array $workItems): array
    {
        foreach ($workItems as $workItem) {
            foreach ($workItem->storeStockLines() as $line) {
                $key = $this->keyer->fromParts(
                    $line->productId(),
                    $line->qty(),
                    $line->lineTotalRupiah()->amount(),
                );

                $counts[$key] = ($counts[$key] ?? 0) + 1;
            }
        }

        return $counts;
    }
}
