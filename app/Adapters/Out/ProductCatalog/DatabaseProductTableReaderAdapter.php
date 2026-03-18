<?php

declare(strict_types=1);

namespace App\Adapters\Out\ProductCatalog;

use App\Adapters\Out\ProductCatalog\Concerns\ProductTableBaseQuery;
use App\Adapters\Out\ProductCatalog\Concerns\ProductTableFilters;
use App\Adapters\Out\ProductCatalog\Concerns\ProductTableOrdering;
use App\Adapters\Out\ProductCatalog\Concerns\ProductTablePayload;
use App\Application\ProductCatalog\DTO\ProductTableQuery;
use App\Ports\Out\ProductCatalog\ProductTableReaderPort;

final class DatabaseProductTableReaderAdapter implements ProductTableReaderPort
{
    use ProductTableBaseQuery;
    use ProductTableFilters;
    use ProductTableOrdering;
    use ProductTablePayload;

    public function search(ProductTableQuery $query): array
    {
        $builder = $this->baseTableQuery();
        $builder = $this->applyTableFilters($builder, $query);
        $builder = $this->applyTableSorting($builder, $query);

        $paginator = $builder->paginate($query->perPage(), ['*'], 'page', $query->page());

        return $this->toTablePayload($paginator, $query);
    }
}
