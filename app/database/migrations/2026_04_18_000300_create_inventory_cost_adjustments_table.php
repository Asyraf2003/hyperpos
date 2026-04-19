<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('inventory_cost_adjustments')) {
            return;
        }

        Schema::create('inventory_cost_adjustments', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('product_id');
            $table->string('source_type');
            $table->string('source_id');
            $table->unsignedInteger('source_revision_no');
            $table->date('tanggal_penyesuaian');
            $table->integer('amount_delta_rupiah');
            $table->text('reason');
            $table->timestamp('created_at')->useCurrent();

            $table->index('product_id');
            $table->index(['source_type', 'source_id'], 'ica_source_lookup_idx');
            $table->index('source_revision_no');
            $table->index('tanggal_penyesuaian');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_cost_adjustments');
    }
};
