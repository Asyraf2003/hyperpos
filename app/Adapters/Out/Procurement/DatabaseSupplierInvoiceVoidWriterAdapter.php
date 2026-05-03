<?php

declare(strict_types=1);

namespace App\Adapters\Out\Procurement;

use App\Ports\Out\Procurement\SupplierInvoiceVoidWriterPort;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class DatabaseSupplierInvoiceVoidWriterAdapter implements SupplierInvoiceVoidWriterPort
{
    public function findVoidSnapshotForUpdate(string $supplierInvoiceId): ?array
    {
        $row = DB::table('supplier_invoices')
            ->where('id', $supplierInvoiceId)
            ->lockForUpdate()
            ->first(['id', 'voided_at']);

        if ($row === null) {
            return null;
        }

        return [
            'supplier_invoice_id' => (string) $row->id,
            'voided_at' => $row->voided_at !== null ? (string) $row->voided_at : null,
        ];
    }

    public function receiptExists(string $supplierInvoiceId): bool
    {
        return DB::table('supplier_receipts')
            ->where('supplier_invoice_id', $supplierInvoiceId)
            ->exists();
    }

    public function paymentExists(string $supplierInvoiceId): bool
    {
        return DB::table('supplier_payments')
            ->where('supplier_invoice_id', $supplierInvoiceId)
            ->exists();
    }

    public function voidInvoice(string $supplierInvoiceId, string $voidReason): void
    {
        DB::table('supplier_invoices')
            ->where('id', $supplierInvoiceId)
            ->update([
                'voided_at' => Carbon::now(),
                'void_reason' => trim($voidReason),
            ]);
    }

    public function recordVoidAuditIfAvailable(
        string $supplierInvoiceId,
        string $voidReason,
        ?string $performedByActorId
    ): void {
        if (! DB::getSchemaBuilder()->hasTable('audit_logs')) {
            return;
        }

        DB::table('audit_logs')->insert([
            'event' => 'supplier_invoice_voided',
            'context' => json_encode([
                'supplier_invoice_id' => $supplierInvoiceId,
                'void_reason' => trim($voidReason),
                'performed_by_actor_id' => $performedByActorId,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'created_at' => Carbon::now(),
        ]);
    }
}
