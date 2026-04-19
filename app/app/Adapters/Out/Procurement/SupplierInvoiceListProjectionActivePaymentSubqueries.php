<?php

declare(strict_types=1);

namespace App\Adapters\Out\Procurement;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

final class SupplierInvoiceListProjectionActivePaymentSubqueries
{
    public function totals(): Builder
    {
        return $this->base()
            ->selectRaw('supplier_invoice_id, COALESCE(SUM(amount_rupiah), 0) as total_paid_rupiah')
            ->groupBy('supplier_invoice_id');
    }

    public function counts(): Builder
    {
        return $this->base()
            ->selectRaw('supplier_invoice_id, COUNT(*) as payment_count')
            ->groupBy('supplier_invoice_id');
    }

    public function proofAttachmentCounts(): Builder
    {
        return $this->base()
            ->leftJoin(
                'supplier_payment_proof_attachments',
                'supplier_payment_proof_attachments.supplier_payment_id',
                '=',
                'supplier_payments.id'
            )
            ->selectRaw(
                'supplier_invoice_id, COUNT(supplier_payment_proof_attachments.id) as proof_attachment_count'
            )
            ->groupBy('supplier_invoice_id');
    }

    private function base(): Builder
    {
        return DB::table('supplier_payments')
            ->leftJoin(
                'supplier_payment_reversals',
                'supplier_payment_reversals.supplier_payment_id',
                '=',
                'supplier_payments.id'
            )
            ->whereNull('supplier_payment_reversals.id');
    }
}
