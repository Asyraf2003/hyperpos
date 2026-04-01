<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Core\Note\Note\Note;
use App\Core\Note\WorkItem\ExternalPurchaseLine;
use App\Core\Note\WorkItem\ServiceDetail;
use App\Core\Note\WorkItem\StoreStockLine;
use App\Core\Note\WorkItem\WorkItem;
use App\Core\Shared\Exceptions\DomainException;
use App\Ports\Out\Inventory\ProductInventoryReaderPort;
use App\Ports\Out\ProductCatalog\ProductReaderPort;

final class TransactionWorkspaceExistingItemMapper
{
    public function __construct(
        private readonly ProductReaderPort $products,
        private readonly ProductInventoryReaderPort $inventories,
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
            WorkItem::TYPE_SERVICE_ONLY => $this->mapServiceOnly($workItem),
            WorkItem::TYPE_SERVICE_WITH_EXTERNAL_PURCHASE => $this->mapServiceExternal($workItem),
            WorkItem::TYPE_STORE_STOCK_SALE_ONLY => $this->mapProductOnly($workItem),
            WorkItem::TYPE_SERVICE_WITH_STORE_STOCK_PART => $this->mapServiceStoreStock($workItem),
            default => throw new DomainException('Tipe work item tidak didukung untuk preload workspace edit.'),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function mapServiceOnly(WorkItem $workItem): array
    {
        $service = $workItem->serviceDetail();

        if (! $service instanceof ServiceDetail) {
            throw new DomainException('Service detail wajib ada untuk work item service_only.');
        }

        return [
            'entry_mode' => 'service',
            'description' => '',
            'part_source' => $service->partSource(),
            'service' => [
                'name' => $service->serviceName(),
                'price_rupiah' => $service->servicePriceRupiah()->amount(),
                'notes' => '',
            ],
            'product_lines' => [],
            'external_purchase_lines' => [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapServiceExternal(WorkItem $workItem): array
    {
        $service = $workItem->serviceDetail();

        if (! $service instanceof ServiceDetail) {
            throw new DomainException('Service detail wajib ada untuk work item service external.');
        }

        $externalLines = $workItem->externalPurchaseLines();

        if (count($externalLines) !== 1) {
            throw new DomainException('Workspace edit hanya mendukung 1 external purchase line per item.');
        }

        $line = $externalLines[0];

        if (! $line instanceof ExternalPurchaseLine) {
            throw new DomainException('External purchase line tidak valid.');
        }

        return [
            'entry_mode' => 'service',
            'description' => '',
            'part_source' => 'external_purchase',
            'service' => [
                'name' => $service->serviceName(),
                'price_rupiah' => $service->servicePriceRupiah()->amount(),
                'notes' => '',
            ],
            'product_lines' => [],
            'external_purchase_lines' => [[
                'label' => $line->costDescription(),
                'qty' => $line->qty(),
                'unit_cost_rupiah' => $line->unitCostRupiah()->amount(),
            ]],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapProductOnly(WorkItem $workItem): array
    {
        $storeLines = $workItem->storeStockLines();

        if (count($storeLines) !== 1) {
            throw new DomainException('Workspace edit hanya mendukung 1 store stock line per item produk.');
        }

        $line = $storeLines[0];

        if (! $line instanceof StoreStockLine) {
            throw new DomainException('Store stock line tidak valid.');
        }

        [$selectedLabel, $availableStock] = $this->buildProductLookupMeta($line->productId());

        return [
            'entry_mode' => 'product',
            'description' => '',
            'part_source' => 'store_stock',
            'service' => [
                'name' => '',
                'price_rupiah' => null,
                'notes' => '',
            ],
            'product_lines' => [[
                'product_id' => $line->productId(),
                'qty' => $line->qty(),
                'unit_price_rupiah' => intdiv($line->lineTotalRupiah()->amount(), $line->qty()),
            ]],
            'external_purchase_lines' => [],
            'selected_label' => $selectedLabel,
            'available_stock' => $availableStock,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapServiceStoreStock(WorkItem $workItem): array
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

        [$selectedLabel, $availableStock] = $this->buildProductLookupMeta($line->productId());

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
            ]],
            'external_purchase_lines' => [],
            'selected_label' => $selectedLabel,
            'available_stock' => $availableStock,
        ];
    }

    /**
     * @return array{0:string,1:int}
     */
    private function buildProductLookupMeta(string $productId): array
    {
        $product = $this->products->getById($productId);

        if ($product === null) {
            throw new DomainException('Produk untuk preload workspace edit tidak ditemukan.');
        }

        $parts = [
            $product->namaBarang(),
            $product->merek(),
        ];

        if ($product->ukuran() !== null) {
            $parts[] = (string) $product->ukuran();
        }

        $label = implode(' — ', $parts);

        if ($product->kodeBarang() !== null) {
            $label .= ' (' . $product->kodeBarang() . ')';
        }

        $inventory = $this->inventories->getByProductId($productId);
        $availableStock = $inventory?->qtyOnHand() ?? 0;

        return [$label, $availableStock];
    }
}
