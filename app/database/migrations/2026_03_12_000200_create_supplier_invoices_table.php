<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_invoices', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('supplier_id');
            $table->string('supplier_nama_pt_pengirim_snapshot')->nullable();

            $table->string('nomor_faktur')->nullable();
            $table->string('nomor_faktur_normalized')->nullable();
            $table->string('document_kind')->default('invoice');
            $table->string('lifecycle_status')->default('active');
            $table->string('origin_supplier_invoice_id')->nullable();
            $table->string('superseded_by_supplier_invoice_id')->nullable();
            $table->date('tanggal_pengiriman');
            $table->date('jatuh_tempo');
            $table->integer('grand_total_rupiah');
            $table->timestamp('voided_at')->nullable();
            $table->text('void_reason')->nullable();
            $table->unsignedInteger('last_revision_no')->default(0);

            $table->index('supplier_id');
            $table->index('tanggal_pengiriman');
            $table->index('jatuh_tempo');
            $table->index('nomor_faktur_normalized', 'si_nomor_faktur_normalized_idx');
            $table->index(['lifecycle_status', 'tanggal_pengiriman'], 'si_lifecycle_shipment_idx');
            $table->index(['lifecycle_status', 'jatuh_tempo'], 'si_lifecycle_due_idx');
            $table->index('origin_supplier_invoice_id', 'si_origin_invoice_idx');
            $table->index('superseded_by_supplier_invoice_id', 'si_superseded_by_invoice_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_invoices');
    }
};
