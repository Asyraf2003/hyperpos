<?php

declare(strict_types=1);

namespace App\Adapters\Out\Procurement;

use App\Application\Procurement\Context\SupplierInvoiceChangeContext;
use App\Core\Procurement\SupplierInvoice\SupplierInvoice;
use App\Core\Procurement\SupplierInvoice\SupplierInvoiceLine;
use App\Ports\Out\ClockPort;
use App\Ports\Out\Procurement\SupplierInvoiceLifecyclePort;
use App\Ports\Out\Procurement\SupplierInvoiceWriterPort;
use App\Ports\Out\TransactionManagerPort;
use App\Ports\Out\UuidPort;
use Illuminate\Support\Facades\DB;

final class DatabaseVersionedSupplierInvoiceWriterAdapter implements SupplierInvoiceWriterPort, SupplierInvoiceLifecyclePort
{
    public function __construct(
        private readonly TransactionManagerPort $transactions,
        private readonly UuidPort $uuid,
        private readonly ClockPort $clock,
        private readonly SupplierInvoiceChangeContext $changeContext,
    ) {
    }

    public function create(SupplierInvoice $supplierInvoice): void
    {
        $revisionNo = 1;
        $occurredAt = $this->clock->now();
        $context = $this->changeContext->snapshot();
        $snapshot = $this->toVersionSnapshot($supplierInvoice);

        DB::table('supplier_invoices')->insert($this->toInvoiceRecord($supplierInvoice, $revisionNo));
        DB::table('supplier_invoice_lines')->insert($this->toLineRecords($supplierInvoice));
        DB::table('supplier_invoice_versions')->insert($this->toVersionRecord(
            $supplierInvoice,
            $revisionNo,
            'supplier_invoice_created',
            $occurredAt,
            $context,
            $snapshot,
        ));

        $auditEventId = $this->uuid->generate();

        DB::table('audit_events')->insert($this->toAuditEventRecord(
            $auditEventId,
            $supplierInvoice,
            $revisionNo,
            'supplier_invoice_created',
            $occurredAt,
            $context,
            $snapshot,
        ));

        DB::table('audit_event_snapshots')->insert($this->toAuditSnapshotRecord(
            $auditEventId,
            'after',
            $snapshot,
            $occurredAt,
        ));
    }

    /**
     * @return array<string, string|int|null>
     */
    private function toInvoiceRecord(SupplierInvoice $supplierInvoice, int $revisionNo): array
    {
        return [
            'id' => $supplierInvoice->id(),
            'supplier_id' => $supplierInvoice->supplierId(),
            'supplier_nama_pt_pengirim_snapshot' => $supplierInvoice->supplierNamaPtPengirimSnapshot(),
            'nomor_faktur' => $supplierInvoice->nomorFaktur(),
            'nomor_faktur_normalized' => $supplierInvoice->nomorFakturNormalized(),
            'document_kind' => $supplierInvoice->documentKind(),
            'lifecycle_status' => $supplierInvoice->lifecycleStatus(),
            'origin_supplier_invoice_id' => $supplierInvoice->originSupplierInvoiceId(),
            'superseded_by_supplier_invoice_id' => $supplierInvoice->supersededBySupplierInvoiceId(),
            'tanggal_pengiriman' => $supplierInvoice->tanggalPengiriman()->format('Y-m-d'),
            'jatuh_tempo' => $supplierInvoice->jatuhTempo()->format('Y-m-d'),
            'grand_total_rupiah' => $supplierInvoice->grandTotalRupiah()->amount(),
            'voided_at' => null,
            'void_reason' => null,
            'last_revision_no' => $revisionNo,
        ];
    }

    /**
     * @return list<array<string, string|int|null>>
     */
    private function toLineRecords(SupplierInvoice $supplierInvoice): array
    {
        return array_map(
            static fn (SupplierInvoiceLine $line): array => [
                'id' => $line->id(),
                'supplier_invoice_id' => $supplierInvoice->id(),
                'line_no' => $line->lineNo(),
                'product_id' => $line->productId(),
                'product_kode_barang_snapshot' => $line->productKodeBarangSnapshot(),
                'product_nama_barang_snapshot' => $line->productNamaBarangSnapshot(),
                'product_merek_snapshot' => $line->productMerekSnapshot(),
                'product_ukuran_snapshot' => $line->productUkuranSnapshot(),
                'qty_pcs' => $line->qtyPcs(),
                'line_total_rupiah' => $line->lineTotalRupiah()->amount(),
                'unit_cost_rupiah' => $line->unitCostRupiah()->amount(),
            ],
            $supplierInvoice->lines(),
        );
    }

    /**
     * @param array{
     *   actor_id:?string,
     *   actor_role:?string,
     *   source_channel:?string,
     *   reason:?string
     * } $context
     * @param array<string, mixed> $snapshot
     * @return array<string, mixed>
     */
    private function toVersionRecord(
        SupplierInvoice $supplierInvoice,
        int $revisionNo,
        string $eventName,
        \DateTimeImmutable $occurredAt,
        array $context,
        array $snapshot,
    ): array {
        return [
            'id' => $this->uuid->generate(),
            'supplier_invoice_id' => $supplierInvoice->id(),
            'revision_no' => $revisionNo,
            'event_name' => $eventName,
            'changed_by_actor_id' => $context['actor_id'],
            'change_reason' => $context['reason'],
            'changed_at' => $occurredAt,
            'snapshot_json' => json_encode($snapshot, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
        ];
    }

    /**
     * @param array{
     *   actor_id:?string,
     *   actor_role:?string,
     *   source_channel:?string,
     *   reason:?string
     * } $context
     * @param array<string, mixed> $snapshot
     * @return array<string, mixed>
     */
    private function toAuditEventRecord(
        string $auditEventId,
        SupplierInvoice $supplierInvoice,
        int $revisionNo,
        string $eventName,
        \DateTimeImmutable $occurredAt,
        array $context,
        array $snapshot,
    ): array {
        return [
            'id' => $auditEventId,
            'bounded_context' => 'procurement',
            'aggregate_type' => 'supplier_invoice',
            'aggregate_id' => $supplierInvoice->id(),
            'event_name' => $eventName,
            'actor_id' => $context['actor_id'],
            'actor_role' => $context['actor_role'],
            'reason' => $context['reason'],
            'source_channel' => $context['source_channel'],
            'request_id' => null,
            'correlation_id' => null,
            'occurred_at' => $occurredAt,
            'metadata_json' => json_encode([
                'supplier_invoice' => $snapshot,
                'revision_no' => $revisionNo,
            ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
        ];
    }

    /**
     * @param array<string, mixed> $snapshot
     * @return array<string, mixed>
     */
    private function toAuditSnapshotRecord(
        string $auditEventId,
        string $snapshotKind,
        array $snapshot,
        \DateTimeImmutable $occurredAt,
    ): array {
        return [
            'id' => $this->uuid->generate(),
            'audit_event_id' => $auditEventId,
            'snapshot_kind' => $snapshotKind,
            'payload_json' => json_encode($snapshot, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
            'created_at' => $occurredAt,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function toVersionSnapshot(SupplierInvoice $supplierInvoice): array
    {
        return [
            'id' => $supplierInvoice->id(),
            'nomor_faktur' => $supplierInvoice->nomorFaktur(),
            'nomor_faktur_normalized' => $supplierInvoice->nomorFakturNormalized(),
            'supplier' => [
                'id' => $supplierInvoice->supplierId(),
                'nama_pt_pengirim_snapshot' => $supplierInvoice->supplierNamaPtPengirimSnapshot(),
            ],
            'document_kind' => $supplierInvoice->documentKind(),
            'lifecycle_status' => $supplierInvoice->lifecycleStatus(),
            'origin_supplier_invoice_id' => $supplierInvoice->originSupplierInvoiceId(),
            'superseded_by_supplier_invoice_id' => $supplierInvoice->supersededBySupplierInvoiceId(),
            'tanggal_pengiriman' => $supplierInvoice->tanggalPengiriman()->format('Y-m-d'),
            'jatuh_tempo' => $supplierInvoice->jatuhTempo()->format('Y-m-d'),
            'grand_total_rupiah' => $supplierInvoice->grandTotalRupiah()->amount(),
            'lines' => array_map(
                static fn (SupplierInvoiceLine $line): array => [
                    'id' => $line->id(),
                    'line_no' => $line->lineNo(),
                    'product_id' => $line->productId(),
                    'product_kode_barang_snapshot' => $line->productKodeBarangSnapshot(),
                    'product_nama_barang_snapshot' => $line->productNamaBarangSnapshot(),
                    'product_merek_snapshot' => $line->productMerekSnapshot(),
                    'product_ukuran_snapshot' => $line->productUkuranSnapshot(),
                    'qty_pcs' => $line->qtyPcs(),
                    'line_total_rupiah' => $line->lineTotalRupiah()->amount(),
                    'unit_cost_rupiah' => $line->unitCostRupiah()->amount(),
                ],
                $supplierInvoice->lines(),
            ),
        ];
    }
}
