<?php

declare(strict_types=1);

namespace App\Adapters\Out\Procurement;

use App\Adapters\Out\Procurement\Concerns\ProcurementInvoiceTablePayload;
use App\Application\Procurement\DTO\ProcurementInvoiceTableQuery;
use App\Ports\Out\Procurement\ProcurementInvoiceTableReaderPort;
use Illuminate\Support\Facades\DB;

final class DatabaseProcurementInvoiceTableReaderAdapter implements ProcurementInvoiceTableReaderPort
{
    use ProcurementInvoiceTablePayload;

    public function __construct(
        private readonly ProcurementInvoiceProjectionTableFilters $filters,
        private readonly ProcurementInvoiceProjectionTableSorting $sorting,
    ) {
    }

    public function search(ProcurementInvoiceTableQuery $query): array
    {
        $builder = DB::table('supplier_invoice_list_projection')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'supplier_invoice_list_projection.supplier_id')
            ->select([
                'supplier_invoice_list_projection.supplier_invoice_id',
                'supplier_invoice_list_projection.nomor_faktur',
                'supplier_invoice_list_projection.supplier_nama_pt_pengirim_snapshot',
                'supplier_invoice_list_projection.shipment_date',
                'supplier_invoice_list_projection.due_date',
                'supplier_invoice_list_projection.grand_total_rupiah',
                'supplier_invoice_list_projection.total_paid_rupiah',
                'supplier_invoice_list_projection.outstanding_rupiah',
                'supplier_invoice_list_projection.payment_count',
                'supplier_invoice_list_projection.receipt_count',
                'supplier_invoice_list_projection.total_received_qty',
                'supplier_invoice_list_projection.proof_attachment_count',
                'supplier_invoice_list_projection.voided_at',
                DB::raw('COALESCE(suppliers.nama_pt_pengirim, supplier_invoice_list_projection.supplier_nama_pt_pengirim_snapshot) as supplier_nama_pt_pengirim_current'),
            ]);

        $builder = $this->filters->apply($builder, $query);
        $builder = $this->sorting->apply($builder, $query);

        $paginator = $builder->paginate($query->perPage(), ['*'], 'page', $query->page());

        return $this->toTablePayload($paginator, $query);
    }
}
