<?php

declare(strict_types=1);

namespace App\Ports\Out\Procurement;

use App\Core\Procurement\SupplierInvoice\SupplierInvoice;

interface SupplierInvoiceWriterPort
{
    public function create(SupplierInvoice $supplierInvoice): void;

    public function update(SupplierInvoice $supplierInvoice): void;
}
