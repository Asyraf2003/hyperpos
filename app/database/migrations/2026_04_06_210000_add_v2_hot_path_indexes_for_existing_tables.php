<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table): void {
            $table->index('event', 'audit_logs_event_idx');
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->index('merek', 'products_merek_idx');
            $table->index('ukuran', 'products_ukuran_idx');
            $table->index('harga_jual', 'products_harga_jual_idx');
            $table->index(['nama_barang', 'merek', 'ukuran'], 'products_duplicate_lookup_idx');
        });

        Schema::table('payment_allocations', function (Blueprint $table): void {
            $table->index(['customer_payment_id', 'note_id'], 'payment_allocations_payment_note_idx');
        });

        Schema::table('payment_component_allocations', function (Blueprint $table): void {
            $table->index(['customer_payment_id', 'note_id'], 'pca_payment_note_idx');
            $table->index('work_item_id', 'pca_work_item_idx');
        });

        Schema::table('refund_component_allocations', function (Blueprint $table): void {
            $table->index('work_item_id', 'rca_work_item_idx');
        });
    }

    public function down(): void
    {
        Schema::table('refund_component_allocations', function (Blueprint $table): void {
            $table->dropIndex('rca_work_item_idx');
        });

        Schema::table('payment_component_allocations', function (Blueprint $table): void {
            $table->dropIndex('pca_work_item_idx');
            $table->dropIndex('pca_payment_note_idx');
        });

        Schema::table('payment_allocations', function (Blueprint $table): void {
            $table->dropIndex('payment_allocations_payment_note_idx');
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->dropIndex('products_duplicate_lookup_idx');
            $table->dropIndex('products_harga_jual_idx');
            $table->dropIndex('products_ukuran_idx');
            $table->dropIndex('products_merek_idx');
        });

        Schema::table('audit_logs', function (Blueprint $table): void {
            $table->dropIndex('audit_logs_event_idx');
        });
    }
};
