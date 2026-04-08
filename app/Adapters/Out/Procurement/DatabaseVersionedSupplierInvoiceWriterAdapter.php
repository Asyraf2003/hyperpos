<?php

declare(strict_types=1);

namespace App\Adapters\Out\Procurement;

use App\Application\Procurement\Context\SupplierInvoiceChangeContext;
use App\Core\Procurement\SupplierInvoice\SupplierInvoice;
use App\Ports\Out\Procurement\SupplierInvoiceLifecyclePort;
use App\Ports\Out\Procurement\SupplierInvoiceWriterPort;
use App\Ports\Out\TransactionManagerPort;
use App\Ports\Out\UuidPort;
use LogicException;

final class DatabaseVersionedSupplierInvoiceWriterAdapter implements SupplierInvoiceWriterPort, SupplierInvoiceLifecyclePort
{
    public function __construct(
        private readonly TransactionManagerPort $transactions,
        private readonly UuidPort $uuid,
        private readonly SupplierInvoiceChangeContext $changeContext,
    ) {
    }

    public function create(SupplierInvoice $supplierInvoice): void
    {
        throw new LogicException('DatabaseVersionedSupplierInvoiceWriterAdapter::create belum diimplementasikan.');
    }
}
