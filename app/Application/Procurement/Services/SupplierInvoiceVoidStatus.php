<?php

declare(strict_types=1);

namespace App\Application\Procurement\Services;

use Illuminate\Support\Facades\DB;

final class SupplierInvoiceVoidStatus
{
    public function isVoided(string $supplierInvoiceId): bool
    {
        return DB::table('supplier_invoices')
            ->where('id', $supplierInvoiceId)
            ->whereNotNull('voided_at')
            ->exists();
    }
}
