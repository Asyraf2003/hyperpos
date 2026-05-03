<?php

declare(strict_types=1);

namespace App\Adapters\Out\Procurement;

use App\Ports\Out\Procurement\SupplierInvoiceVoidStatusReaderPort;
use Illuminate\Support\Facades\DB;

final class DatabaseSupplierInvoiceVoidStatusReaderAdapter implements SupplierInvoiceVoidStatusReaderPort
{
    public function findVoidStatus(string $supplierInvoiceId): ?array
    {
        $row = DB::table('supplier_invoices')
            ->where('id', trim($supplierInvoiceId))
            ->first(['id', 'voided_at']);

        if ($row === null) {
            return null;
        }

        return [
            'supplier_invoice_id' => (string) $row->id,
            'voided_at' => $row->voided_at !== null ? (string) $row->voided_at : null,
        ];
    }

    public function isVoided(string $supplierInvoiceId): bool
    {
        return DB::table('supplier_invoices')
            ->where('id', $supplierInvoiceId)
            ->whereNotNull('voided_at')
            ->exists();
    }
}
