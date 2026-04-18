<?php

declare(strict_types=1);

namespace App\Application\Procurement\Services;

use App\Application\Shared\DTO\Result;
use Illuminate\Support\Facades\DB;

final class VoidedSupplierInvoiceGuard
{
    public function ensureNotVoided(string $supplierInvoiceId): Result
    {
        $row = DB::table('supplier_invoices')
            ->where('id', trim($supplierInvoiceId))
            ->first(['id', 'voided_at']);

        if ($row === null) {
            return Result::failure(
                'Nota supplier tidak ditemukan.',
                ['supplier_invoice' => ['SUPPLIER_INVOICE_NOT_FOUND']]
            );
        }

        if ($row->voided_at !== null) {
            return Result::failure(
                'Nota supplier yang sudah dibatalkan tidak bisa dimutasi lagi.',
                ['supplier_invoice' => ['SUPPLIER_INVOICE_VOIDED']]
            );
        }

        return Result::success();
    }
}
