<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refund_component_allocations', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('customer_refund_id');
            $table->string('customer_payment_id');
            $table->string('note_id');
            $table->string('work_item_id');
            $table->string('component_type');
            $table->string('component_ref_id');
            $table->integer('refunded_amount_rupiah');
            $table->integer('refund_priority');

            $table->index(['note_id', 'work_item_id']);
            $table->index(['customer_payment_id', 'note_id']);
            $table->index(['note_id', 'component_type', 'component_ref_id']);
            $table->unique(['customer_refund_id', 'component_type', 'component_ref_id'], 'refund_component_allocations_unique_refund_component');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refund_component_allocations');
    }
};
