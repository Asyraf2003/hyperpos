<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_invoice_lines', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('supplier_invoice_id');
            $table->string('product_id');
            $table->integer('qty_pcs');
            $table->integer('line_total_rupiah');
            $table->integer('unit_cost_rupiah');

            $table->index('supplier_invoice_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_invoice_lines');
    }
};
