<?php

declare(strict_types=1);

namespace App\Adapters\Out\Procurement;

use App\Ports\Out\Procurement\SupplierReceiptReversalWriterPort;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class DatabaseSupplierReceiptReversalWriterAdapter implements SupplierReceiptReversalWriterPort
{
    public function findReceiptSnapshotForReversal(string $supplierReceiptId): ?array
    {
        $receipt = DB::table('supplier_receipts')
            ->where('id', $supplierReceiptId)
            ->first(['id', 'supplier_invoice_id']);

        if ($receipt === null) {
            return null;
        }

        return [
            'supplier_receipt_id' => (string) $receipt->id,
            'supplier_invoice_id' => (string) $receipt->supplier_invoice_id,
        ];
    }

    public function receiptAlreadyReversed(string $supplierReceiptId): bool
    {
        return DB::table('supplier_receipt_reversals')
            ->where('supplier_receipt_id', $supplierReceiptId)
            ->exists();
    }

    public function record(array $record): void
    {
        $now = Carbon::now();

        DB::table('supplier_receipt_reversals')->insert(array_merge($record, [
            'created_at' => $now,
            'updated_at' => $now,
        ]));
    }
}
