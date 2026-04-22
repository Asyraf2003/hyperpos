<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supplier_invoice_list_projection', function (Blueprint $table): void {
            $table->index(
                ['voided_at', 'shipment_date', 'supplier_invoice_id'],
                'silp_voided_shipment_invoice_idx'
            );

            $table->index(
                ['payment_status', 'shipment_date', 'supplier_invoice_id'],
                'silp_payment_shipment_invoice_idx'
            );

            $table->index(
                ['voided_at', 'due_date', 'supplier_invoice_id'],
                'silp_voided_due_invoice_idx'
            );

            $table->index(
                ['supplier_nama_pt_pengirim_snapshot', 'supplier_invoice_id'],
                'silp_supplier_name_invoice_idx'
            );

            $table->index(
                ['grand_total_rupiah', 'supplier_invoice_id'],
                'silp_grand_total_invoice_idx'
            );

            $table->index(
                ['total_paid_rupiah', 'supplier_invoice_id'],
                'silp_total_paid_invoice_idx'
            );

            $table->index(
                ['outstanding_rupiah', 'supplier_invoice_id'],
                'silp_outstanding_invoice_idx'
            );

            $table->index(
                ['receipt_count', 'supplier_invoice_id'],
                'silp_receipt_count_invoice_idx'
            );

            $table->index(
                ['total_received_qty', 'supplier_invoice_id'],
                'silp_received_qty_invoice_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('supplier_invoice_list_projection', function (Blueprint $table): void {
            $table->dropIndex('silp_voided_shipment_invoice_idx');
            $table->dropIndex('silp_payment_shipment_invoice_idx');
            $table->dropIndex('silp_voided_due_invoice_idx');
            $table->dropIndex('silp_supplier_name_invoice_idx');
            $table->dropIndex('silp_grand_total_invoice_idx');
            $table->dropIndex('silp_total_paid_invoice_idx');
            $table->dropIndex('silp_outstanding_invoice_idx');
            $table->dropIndex('silp_receipt_count_invoice_idx');
            $table->dropIndex('silp_received_qty_invoice_idx');
        });
    }
};
