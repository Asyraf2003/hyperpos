<?php

declare(strict_types=1);

namespace App\Adapters\Out\ProductCatalog;

use App\Adapters\Out\ProductCatalog\Concerns\PersistsVersionedProductWrites;
use App\Adapters\Out\ProductCatalog\Concerns\ProductVersionRevisionLookup;
use App\Adapters\Out\ProductCatalog\Concerns\ProductLifecycleSnapshots;
use App\Adapters\Out\ProductCatalog\Concerns\ProductWritePayloads;
use App\Adapters\Out\ProductCatalog\Concerns\RecordsProductHistory;
use App\Adapters\Out\ProductCatalog\Concerns\RestoresProducts;
use App\Adapters\Out\ProductCatalog\Concerns\SoftDeletesProducts;
use App\Adapters\Out\ProductCatalog\Concerns\TranslatesProductWriteConflicts;
use App\Application\ProductCatalog\Context\ProductChangeContext;
use App\Core\ProductCatalog\Product\Product;
use App\Ports\Out\ProductCatalog\ProductLifecyclePort;
use App\Ports\Out\ProductCatalog\ProductWriterPort;
use App\Ports\Out\TransactionManagerPort;
use App\Ports\Out\UuidPort;
use Illuminate\Database\QueryException;

final class DatabaseVersionedProductWriterAdapter implements ProductWriterPort, ProductLifecyclePort
{
    use PersistsVersionedProductWrites;
    use ProductVersionRevisionLookup;
    use ProductLifecycleSnapshots;
    use ProductWritePayloads;
    use RecordsProductHistory;
    use SoftDeletesProducts;
    use RestoresProducts;
    use TranslatesProductWriteConflicts;

    public function __construct(
        private readonly TransactionManagerPort $transactions,
        private readonly UuidPort $uuid,
        private readonly ProductChangeContext $changeContext,
    ) {
    }

    public function create(Product $product): void
    {
        try {
            $this->persist($product, 'Produk dibuat', true);
        } catch (QueryException $e) {
            throw $this->translateProductWriteConflict($e);
        }
    }

    public function update(Product $product): void
    {
        try {
            $this->persist($product, 'Produk diperbarui', false);
        } catch (QueryException $e) {
            throw $this->translateProductWriteConflict($e);
        }
    }
}
