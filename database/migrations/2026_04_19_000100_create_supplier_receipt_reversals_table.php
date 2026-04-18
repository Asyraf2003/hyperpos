<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_receipt_reversals', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('supplier_receipt_id');
            $table->text('reason');
            $table->string('performed_by_actor_id', 64);
            $table->timestamps();

            $table->unique('supplier_receipt_id', 'uq_srr_receipt');
            $table->foreign('supplier_receipt_id', 'fk_srr_receipt')
                ->references('id')
                ->on('supplier_receipts')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('supplier_receipt_reversals', function (Blueprint $table): void {
            $table->dropForeign('fk_srr_receipt');
            $table->dropUnique('uq_srr_receipt');
        });

        Schema::dropIfExists('supplier_receipt_reversals');
    }
};
