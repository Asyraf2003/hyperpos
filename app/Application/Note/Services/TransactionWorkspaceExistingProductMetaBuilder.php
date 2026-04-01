<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Core\Shared\Exceptions\DomainException;
use App\Ports\Out\Inventory\ProductInventoryReaderPort;
use App\Ports\Out\ProductCatalog\ProductReaderPort;

final class TransactionWorkspaceExistingProductMetaBuilder
{
    public function __construct(
        private readonly ProductReaderPort $products,
        private readonly ProductInventoryReaderPort $inventories,
    ) {
    }

    /**
     * @return array{selected_label:string,available_stock:int}
     */
    public function build(string $productId): array
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

        return [
            'selected_label' => $label,
            'available_stock' => $inventory?->qtyOnHand() ?? 0,
        ];
    }
}
