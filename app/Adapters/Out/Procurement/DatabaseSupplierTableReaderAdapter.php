<?php

declare(strict_types=1);

namespace App\Adapters\Out\Procurement;

use App\Adapters\Out\Procurement\Concerns\SupplierTableBaseQuery;
use App\Adapters\Out\Procurement\Concerns\SupplierTableFilters;
use App\Adapters\Out\Procurement\Concerns\SupplierTableOrdering;
use App\Adapters\Out\Procurement\Concerns\SupplierTablePayload;
use App\Application\Procurement\DTO\SupplierTableQuery;
use App\Ports\Out\Procurement\SupplierTableReaderPort;

final class DatabaseSupplierTableReaderAdapter implements SupplierTableReaderPort
{
    use SupplierTableBaseQuery;
    use SupplierTableFilters;
    use SupplierTableOrdering;
    use SupplierTablePayload;

    public function search(SupplierTableQuery $query): array
    {
        $builder = $this->baseTableQuery();
        $builder = $this->applyTableFilters($builder, $query);
        $builder = $this->applyTableSorting($builder, $query);

        $paginator = $builder->paginate($query->perPage(), ['*'], 'page', $query->page());

        return $this->toTablePayload($paginator, $query);
    }
}
