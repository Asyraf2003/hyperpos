<?php

declare(strict_types=1);

namespace App\Ports\Out\Procurement;

use App\Core\Procurement\SupplierPayment\SupplierPayment;

interface SupplierPaymentWriterPort
{
    public function create(SupplierPayment $supplierPayment): void;
}
