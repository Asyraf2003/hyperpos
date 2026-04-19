<?php

declare(strict_types=1);

namespace App\Adapters\Out\Procurement;

use App\Adapters\Out\Procurement\Concerns\ProcurementInvoiceTablePayload;
use App\Application\Procurement\DTO\ProcurementInvoiceTableQuery;
use App\Ports\Out\Procurement\ProcurementInvoiceTableReaderPort;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

final class DatabaseProcurementInvoiceTableReaderAdapter implements ProcurementInvoiceTableReaderPort
{
    use ProcurementInvoiceTablePayload;

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

        $builder = $this->applyFilters($builder, $query);
        $builder = $this->applySorting($builder, $query);

        $paginator = $builder->paginate($query->perPage(), ['*'], 'page', $query->page());

        return $this->toTablePayload($paginator, $query);
    }

    private function applyFilters(Builder $query, ProcurementInvoiceTableQuery $filters): Builder
    {
        if ($filters->q() !== null) {
            $keyword = trim($filters->q());
            $normalizedKeyword = mb_strtolower($keyword, 'UTF-8');

            $query->where(function (Builder $builder) use ($keyword, $normalizedKeyword): void {
                $builder
                    ->where('supplier_invoice_list_projection.nomor_faktur', 'like', '%' . $keyword . '%')
                    ->orWhere('supplier_invoice_list_projection.nomor_faktur_normalized', 'like', '%' . $normalizedKeyword . '%')
                    ->orWhere('suppliers.nama_pt_pengirim', 'like', '%' . $keyword . '%')
                    ->orWhere('supplier_invoice_list_projection.supplier_nama_pt_pengirim_snapshot', 'like', '%' . $keyword . '%');
            });
        }

        if ($filters->paymentStatus() === 'active') {
            $query->whereNull('supplier_invoice_list_projection.voided_at');
        }

        if ($filters->paymentStatus() === 'outstanding') {
            $query->where('supplier_invoice_list_projection.payment_status', 'outstanding');
        }

        if ($filters->paymentStatus() === 'paid') {
            $query->where('supplier_invoice_list_projection.payment_status', 'paid');
        }

        if ($filters->paymentStatus() === 'voided') {
            $query->where('supplier_invoice_list_projection.payment_status', 'voided');
        }

        if ($filters->shipmentDateFrom() !== null) {
            $query->where('supplier_invoice_list_projection.shipment_date', '>=', $filters->shipmentDateFrom());
        }

        if ($filters->shipmentDateTo() !== null) {
            $query->where('supplier_invoice_list_projection.shipment_date', '<=', $filters->shipmentDateTo());
        }

        return $query;
    }

    private function applySorting(Builder $query, ProcurementInvoiceTableQuery $filters): Builder
    {
        $sortDir = $filters->sortDir() === 'asc' ? 'asc' : 'desc';

        return match ($filters->sortBy()) {
            'due_date' => $query
                ->orderBy('supplier_invoice_list_projection.due_date', $sortDir)
                ->orderBy('supplier_invoice_list_projection.supplier_invoice_id'),
            'nama_pt_pengirim' => $query
                ->orderByRaw(
                    'COALESCE(suppliers.nama_pt_pengirim, supplier_invoice_list_projection.supplier_nama_pt_pengirim_snapshot) ' . $sortDir
                )
                ->orderBy('supplier_invoice_list_projection.supplier_invoice_id'),
            'grand_total_rupiah' => $query
                ->orderBy('supplier_invoice_list_projection.grand_total_rupiah', $sortDir)
                ->orderBy('supplier_invoice_list_projection.supplier_invoice_id'),
            'total_paid_rupiah' => $query
                ->orderBy('supplier_invoice_list_projection.total_paid_rupiah', $sortDir)
                ->orderBy('supplier_invoice_list_projection.supplier_invoice_id'),
            'outstanding_rupiah' => $query
                ->orderBy('supplier_invoice_list_projection.outstanding_rupiah', $sortDir)
                ->orderBy('supplier_invoice_list_projection.supplier_invoice_id'),
            'receipt_count' => $query
                ->orderBy('supplier_invoice_list_projection.receipt_count', $sortDir)
                ->orderBy('supplier_invoice_list_projection.supplier_invoice_id'),
            'total_received_qty' => $query
                ->orderBy('supplier_invoice_list_projection.total_received_qty', $sortDir)
                ->orderBy('supplier_invoice_list_projection.supplier_invoice_id'),
            default => $query
                ->orderBy('supplier_invoice_list_projection.shipment_date', $sortDir)
                ->orderBy('supplier_invoice_list_projection.supplier_invoice_id'),
        };
    }
}
