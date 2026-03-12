<?php

declare(strict_types=1);

namespace App\Ports\Out\Procurement;

use App\Core\Shared\ValueObjects\Money;

interface SupplierPaymentReaderPort
{
    public function getTotalPaidBySupplierInvoiceId(string $supplierInvoiceId): Money;
}
