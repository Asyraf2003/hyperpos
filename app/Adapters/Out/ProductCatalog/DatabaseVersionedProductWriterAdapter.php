<?php

declare(strict_types=1);

namespace App\Adapters\Out\ProductCatalog;

use App\Adapters\Out\ProductCatalog\Concerns\PersistsVersionedProductWrites;
use App\Adapters\Out\ProductCatalog\Concerns\ProductVersionRevisionLookup;
use App\Adapters\Out\ProductCatalog\Concerns\ProductWritePayloads;
use App\Adapters\Out\ProductCatalog\Concerns\RecordsProductHistory;
use App\Adapters\Out\ProductCatalog\Concerns\RestoresProducts;
use App\Adapters\Out\ProductCatalog\Concerns\SoftDeletesProducts;
use App\Application\ProductCatalog\Context\ProductChangeContext;
use App\Core\ProductCatalog\Product\Product;
use App\Ports\Out\ProductCatalog\ProductLifecyclePort;
use App\Ports\Out\ProductCatalog\ProductWriterPort;
use App\Ports\Out\TransactionManagerPort;
use App\Ports\Out\UuidPort;

final class DatabaseVersionedProductWriterAdapter implements ProductWriterPort, ProductLifecyclePort
{
    use PersistsVersionedProductWrites;
    use ProductVersionRevisionLookup;
    use ProductWritePayloads;
    use RecordsProductHistory;
    use SoftDeletesProducts;
    use RestoresProducts;

    public function __construct(
        private readonly TransactionManagerPort $transactions,
        private readonly UuidPort $uuid,
        private readonly ProductChangeContext $changeContext,
    ) {
    }

    public function create(Product $product): void
    {
        $this->persist($product, 'Produk dibuat', true);
    }

    public function update(Product $product): void
    {
        $this->persist($product, 'Produk diperbarui', false);
    }
}
