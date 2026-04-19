<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_items', function (Blueprint $table): void {
            $table->foreign('note_id', 'fk_wi_note')
                ->references('id')
                ->on('notes')
                ->restrictOnDelete();
        });

        Schema::table('work_item_service_details', function (Blueprint $table): void {
            $table->foreign('work_item_id', 'fk_wisd_work_item')
                ->references('id')
                ->on('work_items')
                ->restrictOnDelete();
        });

        Schema::table('work_item_external_purchase_lines', function (Blueprint $table): void {
            $table->foreign('work_item_id', 'fk_wiepl_work_item')
                ->references('id')
                ->on('work_items')
                ->restrictOnDelete();
        });

        Schema::table('work_item_store_stock_lines', function (Blueprint $table): void {
            $table->foreign('work_item_id', 'fk_wissl_work_item')
                ->references('id')
                ->on('work_items')
                ->restrictOnDelete();

            $table->foreign('product_id', 'fk_wissl_product')
                ->references('id')
                ->on('products')
                ->restrictOnDelete();
        });

        Schema::table('payment_allocations', function (Blueprint $table): void {
            $table->foreign('customer_payment_id', 'fk_pa_payment')
                ->references('id')
                ->on('customer_payments')
                ->restrictOnDelete();

            $table->foreign('note_id', 'fk_pa_note')
                ->references('id')
                ->on('notes')
                ->restrictOnDelete();
        });

        Schema::table('customer_refunds', function (Blueprint $table): void {
            $table->foreign('customer_payment_id', 'fk_cr_payment')
                ->references('id')
                ->on('customer_payments')
                ->restrictOnDelete();

            $table->foreign('note_id', 'fk_cr_note')
                ->references('id')
                ->on('notes')
                ->restrictOnDelete();
        });

        Schema::table('payment_component_allocations', function (Blueprint $table): void {
            $table->foreign('customer_payment_id', 'fk_pca_payment')
                ->references('id')
                ->on('customer_payments')
                ->restrictOnDelete();

            $table->foreign('note_id', 'fk_pca_note')
                ->references('id')
                ->on('notes')
                ->restrictOnDelete();

            $table->foreign('work_item_id', 'fk_pca_work_item')
                ->references('id')
                ->on('work_items')
                ->restrictOnDelete();
        });

        Schema::table('refund_component_allocations', function (Blueprint $table): void {
            $table->foreign('customer_refund_id', 'fk_rca_refund')
                ->references('id')
                ->on('customer_refunds')
                ->restrictOnDelete();

            $table->foreign('customer_payment_id', 'fk_rca_payment')
                ->references('id')
                ->on('customer_payments')
                ->restrictOnDelete();

            $table->foreign('note_id', 'fk_rca_note')
                ->references('id')
                ->on('notes')
                ->restrictOnDelete();

            $table->foreign('work_item_id', 'fk_rca_work_item')
                ->references('id')
                ->on('work_items')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('refund_component_allocations', function (Blueprint $table): void {
            $table->dropForeign('fk_rca_work_item');
            $table->dropForeign('fk_rca_note');
            $table->dropForeign('fk_rca_payment');
            $table->dropForeign('fk_rca_refund');
        });

        Schema::table('payment_component_allocations', function (Blueprint $table): void {
            $table->dropForeign('fk_pca_work_item');
            $table->dropForeign('fk_pca_note');
            $table->dropForeign('fk_pca_payment');
        });

        Schema::table('customer_refunds', function (Blueprint $table): void {
            $table->dropForeign('fk_cr_note');
            $table->dropForeign('fk_cr_payment');
        });

        Schema::table('payment_allocations', function (Blueprint $table): void {
            $table->dropForeign('fk_pa_note');
            $table->dropForeign('fk_pa_payment');
        });

        Schema::table('work_item_store_stock_lines', function (Blueprint $table): void {
            $table->dropForeign('fk_wissl_product');
            $table->dropForeign('fk_wissl_work_item');
        });

        Schema::table('work_item_external_purchase_lines', function (Blueprint $table): void {
            $table->dropForeign('fk_wiepl_work_item');
        });

        Schema::table('work_item_service_details', function (Blueprint $table): void {
            $table->dropForeign('fk_wisd_work_item');
        });

        Schema::table('work_items', function (Blueprint $table): void {
            $table->dropForeign('fk_wi_note');
        });
    }
};
