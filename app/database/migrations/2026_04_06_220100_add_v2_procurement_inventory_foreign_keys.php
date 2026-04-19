<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supplier_invoices', function (Blueprint $table): void {
            $table->foreign('supplier_id', 'fk_si_supplier')
                ->references('id')
                ->on('suppliers')
                ->restrictOnDelete();
        });

        Schema::table('supplier_invoice_lines', function (Blueprint $table): void {
            $table->foreign('supplier_invoice_id', 'fk_sil_invoice')
                ->references('id')
                ->on('supplier_invoices')
                ->restrictOnDelete();

            $table->foreign('product_id', 'fk_sil_product')
                ->references('id')
                ->on('products')
                ->restrictOnDelete();
        });

        Schema::table('supplier_receipts', function (Blueprint $table): void {
            $table->foreign('supplier_invoice_id', 'fk_sr_invoice')
                ->references('id')
                ->on('supplier_invoices')
                ->restrictOnDelete();
        });

        Schema::table('supplier_receipt_lines', function (Blueprint $table): void {
            $table->foreign('supplier_receipt_id', 'fk_srl_receipt')
                ->references('id')
                ->on('supplier_receipts')
                ->restrictOnDelete();

            $table->foreign('supplier_invoice_line_id', 'fk_srl_invoice_line')
                ->references('id')
                ->on('supplier_invoice_lines')
                ->restrictOnDelete();
        });

        Schema::table('inventory_movements', function (Blueprint $table): void {
            $table->foreign('product_id', 'fk_im_product')
                ->references('id')
                ->on('products')
                ->restrictOnDelete();
        });

        Schema::table('product_inventory', function (Blueprint $table): void {
            $table->foreign('product_id', 'fk_pi_product')
                ->references('id')
                ->on('products')
                ->restrictOnDelete();
        });

        Schema::table('product_inventory_costing', function (Blueprint $table): void {
            $table->foreign('product_id', 'fk_pic_product')
                ->references('id')
                ->on('products')
                ->restrictOnDelete();
        });

        Schema::table('supplier_payments', function (Blueprint $table): void {
            $table->foreign('supplier_invoice_id', 'fk_sp_invoice')
                ->references('id')
                ->on('supplier_invoices')
                ->restrictOnDelete();
        });

        Schema::table('supplier_payment_proof_attachments', function (Blueprint $table): void {
            $table->foreign('supplier_payment_id', 'fk_sppa_payment')
                ->references('id')
                ->on('supplier_payments')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('supplier_payment_proof_attachments', function (Blueprint $table): void {
            $table->dropForeign('fk_sppa_payment');
        });

        Schema::table('supplier_payments', function (Blueprint $table): void {
            $table->dropForeign('fk_sp_invoice');
        });

        Schema::table('product_inventory_costing', function (Blueprint $table): void {
            $table->dropForeign('fk_pic_product');
        });

        Schema::table('product_inventory', function (Blueprint $table): void {
            $table->dropForeign('fk_pi_product');
        });

        Schema::table('inventory_movements', function (Blueprint $table): void {
            $table->dropForeign('fk_im_product');
        });

        Schema::table('supplier_receipt_lines', function (Blueprint $table): void {
            $table->dropForeign('fk_srl_invoice_line');
            $table->dropForeign('fk_srl_receipt');
        });

        Schema::table('supplier_receipts', function (Blueprint $table): void {
            $table->dropForeign('fk_sr_invoice');
        });

        Schema::table('supplier_invoice_lines', function (Blueprint $table): void {
            $table->dropForeign('fk_sil_product');
            $table->dropForeign('fk_sil_invoice');
        });

        Schema::table('supplier_invoices', function (Blueprint $table): void {
            $table->dropForeign('fk_si_supplier');
        });
    }
};
