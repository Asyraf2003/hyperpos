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
            $table->date('tanggal_pengiriman');
            $table->date('jatuh_tempo');
            $table->integer('grand_total_rupiah');

            $table->index('supplier_id');
            $table->index('tanggal_pengiriman');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_invoices');
    }
};
