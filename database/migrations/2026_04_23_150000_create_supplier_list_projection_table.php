<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_list_projection', function (Blueprint $table): void {
            $table->string('supplier_id')->primary();
            $table->string('nama_pt_pengirim');
            $table->unsignedInteger('invoice_count')->default(0);
            $table->bigInteger('outstanding_rupiah')->default(0);
            $table->unsignedInteger('invoice_unpaid_count')->default(0);
            $table->date('last_shipment_date')->nullable();
            $table->timestamp('projected_at');

            $table->index(['nama_pt_pengirim', 'supplier_id'], 'slp_name_supplier_idx');
            $table->index(['invoice_unpaid_count', 'supplier_id'], 'slp_unpaid_supplier_idx');
            $table->index(['outstanding_rupiah', 'supplier_id'], 'slp_outstanding_supplier_idx');
            $table->index(['last_shipment_date', 'supplier_id'], 'slp_last_shipment_supplier_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_list_projection');
    }
};
