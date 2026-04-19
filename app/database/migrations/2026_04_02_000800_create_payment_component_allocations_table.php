<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_component_allocations', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('customer_payment_id');
            $table->string('note_id');
            $table->string('work_item_id');
            $table->string('component_type');
            $table->string('component_ref_id');
            $table->integer('component_amount_rupiah_snapshot');
            $table->integer('allocated_amount_rupiah');
            $table->integer('allocation_priority');

            $table->index(['note_id', 'work_item_id'], 'pca_note_work_item_idx');
            $table->index(['note_id', 'component_type', 'component_ref_id'], 'pca_note_component_idx');
            $table->unique(['customer_payment_id', 'component_type', 'component_ref_id'], 'payment_component_allocations_unique_payment_component');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_component_allocations');
    }
};
