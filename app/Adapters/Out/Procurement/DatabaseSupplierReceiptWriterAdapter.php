<?php

declare(strict_types=1);

namespace App\Adapters\Out\Procurement;

use App\Core\Procurement\SupplierReceipt\SupplierReceipt;
use App\Core\Procurement\SupplierReceipt\SupplierReceiptLine;
use App\Ports\Out\Procurement\SupplierReceiptWriterPort;
use Illuminate\Support\Facades\DB;

final class DatabaseSupplierReceiptWriterAdapter implements SupplierReceiptWriterPort
{
    public function create(SupplierReceipt $supplierReceipt): void
    {
        DB::table('supplier_receipts')->insert($this->toReceiptRecord($supplierReceipt));

        DB::table('supplier_receipt_lines')->insert($this->toReceiptLineRecords($supplierReceipt));
    }

    /**
     * @return array<string, string>
     */
    private function toReceiptRecord(SupplierReceipt $supplierReceipt): array
    {
        return [
            'id' => $supplierReceipt->id(),
            'supplier_invoice_id' => $supplierReceipt->supplierInvoiceId(),
            'tanggal_terima' => $supplierReceipt->tanggalTerima()->format('Y-m-d'),
        ];
    }

    /**
     * @return list<array<string, string|int>>
     */
    private function toReceiptLineRecords(SupplierReceipt $supplierReceipt): array
    {
        return array_map(
            static fn (SupplierReceiptLine $line): array => [
                'id' => $line->id(),
                'supplier_receipt_id' => $supplierReceipt->id(),
                'supplier_invoice_line_id' => $line->supplierInvoiceLineId(),
                'qty_diterima' => $line->qtyDiterima(),
            ],
            $supplierReceipt->lines(),
        );
    }
}
