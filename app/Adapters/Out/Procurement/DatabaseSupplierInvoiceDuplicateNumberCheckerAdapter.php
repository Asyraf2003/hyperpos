<?php

declare(strict_types=1);

namespace App\Adapters\Out\Procurement;

use App\Ports\Out\Procurement\SupplierInvoiceDuplicateNumberCheckerPort;
use Illuminate\Support\Facades\DB;

final class DatabaseSupplierInvoiceDuplicateNumberCheckerAdapter implements SupplierInvoiceDuplicateNumberCheckerPort
{
    public function existsActiveByNormalizedNumber(
        string $normalizedNomorFaktur,
        ?string $excludeSupplierInvoiceId = null,
    ): bool {
        $normalized = mb_strtolower(trim($normalizedNomorFaktur), 'UTF-8');

        if ($normalized === '') {
            return false;
        }

        $query = DB::table('supplier_invoices')
            ->where('nomor_faktur_normalized', $normalized)
            ->whereNull('voided_at');

        if ($excludeSupplierInvoiceId !== null && trim($excludeSupplierInvoiceId) !== '') {
            $query->where('id', '!=', trim($excludeSupplierInvoiceId));
        }

        return $query->exists();
    }
}
