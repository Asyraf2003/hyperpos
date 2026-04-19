<?php

declare(strict_types=1);

namespace App\Application\Procurement\Services;

use App\Application\Shared\DTO\Result;
use Illuminate\Database\QueryException;

final class SupplierInvoiceQueryExceptionClassifier
{
    public function classify(QueryException $e): ?Result
    {
        $sqlState = (string) ($e->errorInfo[0] ?? $e->getCode());
        $message = mb_strtolower($e->getMessage());

        if (
            $sqlState === '23000'
            && str_contains($message, 'sil_supplier_invoice_revision_product_unique')
        ) {
            return Result::failure(
                'Produk yang sama tidak boleh muncul lebih dari sekali dalam satu faktur supplier.',
                ['supplier_invoice' => ['SUPPLIER_INVOICE_DUPLICATE_PRODUCT']]
            );
        }

        if (
            ($sqlState === '22003' || str_contains($message, 'out of range'))
            && (str_contains($message, 'grand_total_rupiah') || str_contains($message, 'supplier_invoices'))
        ) {
            return Result::failure(
                'Total keseluruhan nota melebihi batas penyimpanan sistem. Kurangi total rincian lalu simpan lagi.',
                ['supplier_invoice' => ['SUPPLIER_INVOICE_GRAND_TOTAL_OUT_OF_RANGE']]
            );
        }

        return null;
    }
}
