<?php

declare(strict_types=1);

namespace App\Application\Procurement\UseCases;

use App\Application\Shared\DTO\Result;
use App\Ports\Out\Procurement\ProcurementInvoiceDetailReaderPort;

final class GetProcurementInvoiceDetailHandler
{
    public function __construct(
        private readonly ProcurementInvoiceDetailReaderPort $details,
    ) {
    }

    public function handle(string $supplierInvoiceId): Result
    {
        $detail = $this->details->getById($supplierInvoiceId);

        if ($detail === null) {
            return Result::failure(
                'Nota supplier tidak ditemukan.',
                ['supplier_invoice' => ['SUPPLIER_INVOICE_NOT_FOUND']],
            );
        }

        return Result::success($detail);
    }
}
