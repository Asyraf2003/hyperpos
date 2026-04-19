<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_invoice_list_projection', function (Blueprint $table): void {
            $table->string('supplier_invoice_id')->primary();
            $table->string('supplier_id');
            $table->string('nomor_faktur')->nullable();
            $table->string('nomor_faktur_normalized')->nullable();
            $table->string('supplier_nama_pt_pengirim_snapshot')->nullable();
            $table->date('shipment_date');
            $table->date('due_date');
            $table->integer('grand_total_rupiah');
            $table->integer('total_paid_rupiah')->default(0);
            $table->integer('outstanding_rupiah')->default(0);
            $table->unsignedInteger('payment_count')->default(0);
            $table->unsignedInteger('receipt_count')->default(0);
            $table->integer('total_received_qty')->default(0);
            $table->unsignedInteger('proof_attachment_count')->default(0);
            $table->string('lifecycle_status')->default('active');
            $table->string('payment_status')->default('outstanding');
            $table->timestamp('voided_at')->nullable();
            $table->unsignedInteger('last_revision_no')->default(0);
            $table->timestamp('projected_at');

            $table->index('supplier_id', 'silp_supplier_id_idx');
            $table->index('shipment_date', 'silp_shipment_date_idx');
            $table->index('due_date', 'silp_due_date_idx');
            $table->index('nomor_faktur_normalized', 'silp_nomor_faktur_normalized_idx');
            $table->index('payment_status', 'silp_payment_status_idx');
            $table->index(['payment_status', 'shipment_date'], 'silp_payment_status_shipment_idx');
            $table->index(['payment_status', 'due_date'], 'silp_payment_status_due_idx');
            $table->index('lifecycle_status', 'silp_lifecycle_status_idx');
            $table->index('voided_at', 'silp_voided_at_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_invoice_list_projection');
    }
};
