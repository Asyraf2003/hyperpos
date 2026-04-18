<?php

declare(strict_types=1);

namespace App\Application\Procurement\UseCases;

use App\Application\Shared\DTO\Result;
use Illuminate\Database\QueryException;

final class CreateSupplierInvoiceFlowQueryExceptionClassifier
{
    public function classify(QueryException $e): ?Result
    {
        $sqlState = (string) ($e->errorInfo[0] ?? $e->getCode());
        $message = mb_strtolower($e->getMessage());

        if ($sqlState !== '22003' && ! str_contains($message, 'out of range')) {
            return null;
        }

        if (! str_contains($message, 'grand_total_rupiah') && ! str_contains($message, 'supplier_invoices')) {
            return null;
        }

        return Result::failure(
            'Total keseluruhan nota melebihi batas penyimpanan sistem. Kurangi total rincian lalu simpan lagi.',
            ['supplier_invoice' => ['SUPPLIER_INVOICE_GRAND_TOTAL_OUT_OF_RANGE']]
        );
    }
}
