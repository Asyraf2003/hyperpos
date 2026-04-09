<?php

declare(strict_types=1);

namespace App\Adapters\Out\Procurement\Concerns;

use App\Core\Procurement\SupplierInvoice\SupplierInvoice;
use Illuminate\Support\Facades\DB;

trait PersistsVersionedSupplierInvoiceWrites
{
    private function persistCreatedInvoice(SupplierInvoice $supplierInvoice): void
    {
        $revisionNo = 1;
        $occurredAt = $this->clock->now();
        $context = $this->changeContext->snapshot();
        $snapshot = $this->toVersionSnapshot($supplierInvoice);
        $eventName = 'supplier_invoice_created';

        DB::table('supplier_invoices')->insert($this->toInvoiceRecord($supplierInvoice, $revisionNo));
        DB::table('supplier_invoice_lines')->insert($this->toLineRecords($supplierInvoice));
        DB::table('supplier_invoice_versions')->insert($this->toVersionRecord(
            $supplierInvoice,
            $revisionNo,
            $eventName,
            $occurredAt,
            $context,
            $snapshot,
        ));

        $auditEventId = $this->uuid->generate();

        DB::table('audit_events')->insert($this->toAuditEventRecord(
            $auditEventId,
            $supplierInvoice,
            $revisionNo,
            $eventName,
            $occurredAt,
            $context,
            $snapshot,
        ));

        DB::table('audit_event_snapshots')->insert(
            $this->toAuditSnapshotRecord($auditEventId, 'after', $snapshot, $occurredAt)
        );
    }
}
