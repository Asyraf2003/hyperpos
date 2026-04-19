<?php

declare(strict_types=1);

namespace App\Adapters\Out\Procurement\Concerns;

use App\Application\Procurement\DTO\ProcurementInvoiceTableQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

trait ProcurementInvoiceTablePayload
{
    use BuildsProcurementInvoiceTableRowPayload;

    /**
     * @return array{
     *   rows:list<array<string, bool|int|string>>,
     *   meta:array<string, mixed>
     * }
     */
    private function toTablePayload(LengthAwarePaginator $paginator, ProcurementInvoiceTableQuery $query): array
    {
        return [
            'rows' => array_map(fn (object $row): array => $this->toTableRowPayload($row), $paginator->items()),
            'meta' => [
                'page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'sort_by' => $query->sortBy(),
                'sort_dir' => $query->sortDir(),
                'filters' => [
                    'q' => $query->q(),
                    'payment_status' => $query->paymentStatus(),
                    'shipment_date_from' => $query->shipmentDateFrom(),
                    'shipment_date_to' => $query->shipmentDateTo(),
                ],
            ],
        ];
    }
}
