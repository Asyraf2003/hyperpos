<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Core\Note\Note\Note;
use App\Core\Note\Revision\NoteRevision;
use App\Core\Note\Revision\NoteRevisionLineSnapshot;
use App\Core\Note\WorkItem\WorkItem;
use DateTimeImmutable;

final class NoteRevisionBootstrapFactory
{
    public function createInitialRevision(
        string $revisionId,
        Note $note,
        ?string $actorId,
        DateTimeImmutable $createdAt,
        ?string $reason = 'Bootstrap initial revision from current root note state',
    ): NoteRevision {
        $lines = [];
        $grandTotal = 0;

        foreach ($note->workItems() as $item) {
            $lineRevisionId = sprintf('%s-line-%02d', trim($revisionId), $item->lineNo());
            $lines[] = $this->mapLine($lineRevisionId, trim($revisionId), $item);
            $grandTotal += $item->subtotalRupiah()->amount();
        }

        return NoteRevision::create(
            trim($revisionId),
            $note->id(),
            1,
            null,
            $actorId,
            $reason,
            $note->customerName(),
            $note->customerPhone(),
            $note->transactionDate(),
            $grandTotal,
            $lines,
            $createdAt,
        );
    }

    public function createNextRevision(
        string $revisionId,
        string $parentRevisionId,
        int $revisionNumber,
        Note $note,
        ?string $actorId,
        DateTimeImmutable $createdAt,
        ?string $reason,
    ): NoteRevision {
        $lines = [];
        $grandTotal = 0;

        foreach ($note->workItems() as $item) {
            $lineRevisionId = sprintf('%s-line-%02d', trim($revisionId), $item->lineNo());
            $lines[] = $this->mapLine($lineRevisionId, trim($revisionId), $item);
            $grandTotal += $item->subtotalRupiah()->amount();
        }

        return NoteRevision::create(
            trim($revisionId),
            $note->id(),
            $revisionNumber,
            trim($parentRevisionId),
            $actorId,
            $reason,
            $note->customerName(),
            $note->customerPhone(),
            $note->transactionDate(),
            $grandTotal,
            $lines,
            $createdAt,
        );
    }

    private function mapLine(
        string $lineRevisionId,
        string $noteRevisionId,
        WorkItem $item,
    ): NoteRevisionLineSnapshot {
        $service = $item->serviceDetail();

        $payload = [
            'work_item_root_id' => $item->id(),
            'transaction_type' => $item->transactionType(),
            'status' => $item->status(),
            'external_purchase_lines' => array_map(
                static fn (mixed $line): mixed => is_object($line) && method_exists($line, 'toArray')
                    ? $line->toArray()
                    : $line,
                $item->externalPurchaseLines(),
            ),
            'store_stock_lines' => array_map(
                static fn (mixed $line): mixed => is_object($line) && method_exists($line, 'toArray')
                    ? $line->toArray()
                    : $line,
                $item->storeStockLines(),
            ),
        ];

        if ($service !== null) {
            $payload['service'] = [
                'service_name' => $service->serviceName(),
                'service_price_rupiah' => $service->servicePriceRupiah()->amount(),
                'part_source' => $service->partSource(),
            ];
        }

        return NoteRevisionLineSnapshot::create(
            $lineRevisionId,
            $noteRevisionId,
            $item->id(),
            $item->lineNo(),
            $item->transactionType(),
            $item->status(),
            $item->subtotalRupiah()->amount(),
            $service?->serviceName(),
            $service?->servicePriceRupiah()->amount(),
            $payload,
        );
    }
}
