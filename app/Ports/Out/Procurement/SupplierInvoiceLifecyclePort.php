<?php

declare(strict_types=1);

namespace App\Ports\Out\Procurement;

use App\Core\Procurement\SupplierInvoice\SupplierInvoice;

interface SupplierInvoiceLifecyclePort extends SupplierInvoiceWriterPort
{
    public function create(SupplierInvoice $supplierInvoice): void;
}
