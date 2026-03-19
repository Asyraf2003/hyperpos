<?php

declare(strict_types=1);

namespace App\Adapters\Out\Procurement;

use App\Adapters\Out\Procurement\Concerns\ProcurementInvoiceTableBaseQuery;
use App\Adapters\Out\Procurement\Concerns\ProcurementInvoiceTableFilters;
use App\Adapters\Out\Procurement\Concerns\ProcurementInvoiceTableOrdering;
use App\Adapters\Out\Procurement\Concerns\ProcurementInvoiceTablePayload;
use App\Application\Procurement\DTO\ProcurementInvoiceTableQuery;
use App\Ports\Out\Procurement\ProcurementInvoiceTableReaderPort;

final class DatabaseProcurementInvoiceTableReaderAdapter implements ProcurementInvoiceTableReaderPort
{
    use ProcurementInvoiceTableBaseQuery;
    use ProcurementInvoiceTableFilters;
    use ProcurementInvoiceTableOrdering;
    use ProcurementInvoiceTablePayload;

    public function search(ProcurementInvoiceTableQuery $query): array
    {
        $builder = $this->baseTableQuery();
        $builder = $this->applyTableFilters($builder, $query);
        $builder = $this->applyTableSorting($builder, $query);

        $paginator = $builder->paginate($query->perPage(), ['*'], 'page', $query->page());

        return $this->toTablePayload($paginator, $query);
    }
}
