<?php

declare(strict_types=1);

namespace App\Application\Procurement\UseCases;

use App\Application\Procurement\DTO\ProcurementInvoiceTableQuery;
use App\Application\Shared\DTO\Result;
use App\Ports\Out\Procurement\ProcurementInvoiceTableReaderPort;

final class GetProcurementInvoiceTableHandler
{
    public function __construct(
        private readonly ProcurementInvoiceTableReaderPort $invoices,
    ) {
    }

    public function handle(ProcurementInvoiceTableQuery $query): Result
    {
        return Result::success($this->invoices->search($query));
    }
}
