<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Core\Note\Note\Note;
use App\Core\Note\WorkItem\WorkItem;
use App\Core\Shared\Exceptions\DomainException;

final class TransactionWorkspaceExistingItemMapper
{
    public function __construct(
        private readonly TransactionWorkspaceExistingServiceOnlyMapper $serviceOnly,
        private readonly TransactionWorkspaceExistingServiceExternalMapper $serviceExternal,
        private readonly TransactionWorkspaceExistingProductOnlyMapper $productOnly,
        private readonly TransactionWorkspaceExistingServiceStoreStockMapper $serviceStoreStock,
    ) {
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function mapMany(Note $note): array
    {
        $items = [];

        foreach ($note->workItems() as $workItem) {
            $items[] = $this->mapItem($workItem);
        }

        return $items;
    }

    /**
     * @return array<string, mixed>
     */
    private function mapItem(WorkItem $workItem): array
    {
        return match ($workItem->transactionType()) {
            WorkItem::TYPE_SERVICE_ONLY => $this->serviceOnly->map($workItem),
            WorkItem::TYPE_SERVICE_WITH_EXTERNAL_PURCHASE => $this->serviceExternal->map($workItem),
            WorkItem::TYPE_STORE_STOCK_SALE_ONLY => $this->productOnly->map($workItem),
            WorkItem::TYPE_SERVICE_WITH_STORE_STOCK_PART => $this->serviceStoreStock->map($workItem),
            default => throw new DomainException('Tipe work item tidak didukung untuk preload workspace edit.'),
        };
    }
}
