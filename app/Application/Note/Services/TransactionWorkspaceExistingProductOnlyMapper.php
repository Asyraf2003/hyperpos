<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Core\Note\WorkItem\StoreStockLine;
use App\Core\Note\WorkItem\WorkItem;
use App\Core\Shared\Exceptions\DomainException;

final class TransactionWorkspaceExistingProductOnlyMapper
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
        $storeLines = $workItem->storeStockLines();

        if (count($storeLines) !== 1) {
            throw new DomainException('Workspace edit hanya mendukung 1 store stock line per item produk.');
        }

        $line = $storeLines[0];

        if (! $line instanceof StoreStockLine) {
            throw new DomainException('Store stock line tidak valid.');
        }

        $meta = $this->productMeta->build($line->productId());

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
                'price_basis' => 'revision_snapshot',
            ]],
            'external_purchase_lines' => [],
            'selected_label' => $meta['selected_label'],
            'available_stock' => $meta['available_stock'],
        ];
    }
}
