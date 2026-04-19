<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_payments', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('supplier_invoice_id');
            $table->integer('amount_rupiah');
            $table->date('paid_at');
            $table->string('proof_status');
            $table->string('proof_storage_path')->nullable();

            $table->index('supplier_invoice_id');
            $table->index('paid_at');
            $table->index('proof_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_payments');
    }
};
