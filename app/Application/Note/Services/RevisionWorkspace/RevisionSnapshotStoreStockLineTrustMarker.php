<?php

declare(strict_types=1);

namespace App\Application\Note\Services\RevisionWorkspace;

use App\Core\Note\Revision\NoteRevision;
use App\Core\Note\WorkItem\WorkItem;

final class RevisionSnapshotStoreStockLineTrustMarker
{
    public function __construct(
        private readonly RevisionSnapshotStoreStockLineTrustInventory $inventory,
        private readonly RevisionSnapshotStoreStockLineKeyer $keyer,
    ) {
    }

    /**
     * @param list<array<string, mixed>> $items
     * @param list<WorkItem> $currentWorkItems
     * @return list<array<string, mixed>>
     */
    public function mark(array $items, ?NoteRevision $currentRevision, array $currentWorkItems = []): array
    {
        $available = $this->inventory->countAvailable($currentRevision, $currentWorkItems);

        foreach ($items as $itemIndex => $item) {
            if (! is_array($item)) {
                continue;
            }

            $line = $this->firstProductLine($item);
            if ($line === null) {
                continue;
            }

            $line = $this->markLine($line, $available);
            $items[$itemIndex]['product_lines'][0] = $line;
        }

        return $items;
    }

    /**
     * @param array<string, mixed> $item
     * @return array<string, mixed>|null
     */
    private function firstProductLine(array $item): ?array
    {
        $lines = $item['product_lines'] ?? null;

        if (! is_array($lines) || ! isset($lines[0]) || ! is_array($lines[0])) {
            return null;
        }

        return $lines[0];
    }

    /**
     * @param array<string, mixed> $line
     * @param array<string, int> $available
     * @return array<string, mixed>
     */
    private function markLine(array $line, array &$available): array
    {
        $line['_server_trusted_revision_snapshot'] = false;

        if (($line['price_basis'] ?? null) !== 'revision_snapshot') {
            return $line;
        }

        $key = $this->keyer->fromParts(
            (string) ($line['product_id'] ?? ''),
            (int) ($line['qty'] ?? 0),
            (int) ($line['qty'] ?? 0) * (int) ($line['unit_price_rupiah'] ?? 0),
        );

        if (($available[$key] ?? 0) > 0) {
            $line['_server_trusted_revision_snapshot'] = true;
            $available[$key]--;
        }

        return $line;
    }
}
