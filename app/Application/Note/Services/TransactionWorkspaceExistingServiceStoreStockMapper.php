<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Core\Note\WorkItem\ServiceDetail;
use App\Core\Note\WorkItem\StoreStockLine;
use App\Core\Note\WorkItem\WorkItem;
use App\Core\Shared\Exceptions\DomainException;

final class TransactionWorkspaceExistingServiceStoreStockMapper
{
    public function __construct(
        private readonly TransactionWorkspaceExistingProductMetaBuilder $productMeta,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function map(WorkItem $workItem): array
    {
        $service = $workItem->serviceDetail();

        if (! $service instanceof ServiceDetail) {
            throw new DomainException('Service detail wajib ada untuk work item service store stock.');
        }

        $storeLines = $workItem->storeStockLines();

        if (count($storeLines) !== 1) {
            throw new DomainException('Workspace edit hanya mendukung 1 store stock line per item service.');
        }

        $line = $storeLines[0];

        if (! $line instanceof StoreStockLine) {
            throw new DomainException('Store stock line tidak valid.');
        }

        $meta = $this->productMeta->build($line->productId());

        return [
            'entry_mode' => 'service',
            'description' => '',
            'part_source' => 'store_stock',
            'service' => [
                'name' => $service->serviceName(),
                'price_rupiah' => $service->servicePriceRupiah()->amount(),
                'notes' => '',
            ],
            'product_lines' => [[
                'product_id' => $line->productId(),
                'qty' => $line->qty(),
                'unit_price_rupiah' => intdiv($line->lineTotalRupiah()->amount(), $line->qty()),
                'price_basis' => 'revision_snapshot',
            ]],
            'external_purchase_lines' => [],
            'selected_label' => $meta['selected_label'],
            'available_stock' => $meta['available_stock'],
        ];
    }
}
