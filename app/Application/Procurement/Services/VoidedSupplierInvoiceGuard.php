<?php

declare(strict_types=1);

namespace App\Application\Procurement\Services;

use App\Application\Shared\DTO\Result;
use App\Ports\Out\Procurement\SupplierInvoiceVoidStatusReaderPort;

final class VoidedSupplierInvoiceGuard
{
    public function __construct(
        private readonly SupplierInvoiceVoidStatusReaderPort $voidStatus,
    ) {
    }

    public function ensureNotVoided(string $supplierInvoiceId): Result
    {
        $row = $this->voidStatus->findVoidStatus($supplierInvoiceId);

        if ($row === null) {
            return Result::failure(
                'Nota supplier tidak ditemukan.',
                ['supplier_invoice' => ['SUPPLIER_INVOICE_NOT_FOUND']]
            );
        }

        if ($row['voided_at'] !== null) {
            return Result::failure(
                'Nota supplier yang sudah dibatalkan tidak bisa dimutasi lagi.',
                ['supplier_invoice' => ['SUPPLIER_INVOICE_VOIDED']]
            );
        }

        return Result::success();
    }
}
