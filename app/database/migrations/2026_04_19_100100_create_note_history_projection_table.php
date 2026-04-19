<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('note_history_projection', function (Blueprint $table): void {
            $table->string('note_id')->primary();
            $table->date('transaction_date');
            $table->string('note_state')->default('open');
            $table->string('customer_name');
            $table->string('customer_name_normalized');
            $table->string('customer_phone')->nullable();
            $table->integer('total_rupiah');
            $table->integer('allocated_rupiah')->default(0);
            $table->integer('refunded_rupiah')->default(0);
            $table->integer('net_paid_rupiah')->default(0);
            $table->integer('outstanding_rupiah')->default(0);
            $table->unsignedInteger('line_open_count')->default(0);
            $table->unsignedInteger('line_close_count')->default(0);
            $table->unsignedInteger('line_refund_count')->default(0);
            $table->boolean('has_open_lines')->default(false);
            $table->boolean('has_close_lines')->default(false);
            $table->boolean('has_refund_lines')->default(false);
            $table->timestamp('projected_at');

            $table->index('transaction_date', 'nhp_transaction_date_idx');
            $table->index('note_state', 'nhp_note_state_idx');
            $table->index('customer_name_normalized', 'nhp_customer_name_normalized_idx');
            $table->index('customer_phone', 'nhp_customer_phone_idx');
            $table->index(['has_open_lines', 'transaction_date'], 'nhp_open_lines_transaction_date_idx');
            $table->index(['has_close_lines', 'transaction_date'], 'nhp_close_lines_transaction_date_idx');
            $table->index(['has_refund_lines', 'transaction_date'], 'nhp_refund_lines_transaction_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('note_history_projection');
    }
};
