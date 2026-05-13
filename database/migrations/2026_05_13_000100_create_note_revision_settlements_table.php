<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('note_revision_settlements', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('note_revision_id');
            $table->string('note_root_id');

            $table->bigInteger('gross_total_rupiah')->default(0);
            $table->bigInteger('carry_forward_paid_rupiah')->default(0);
            $table->bigInteger('carry_forward_refunded_rupiah')->default(0);
            $table->bigInteger('net_paid_rupiah')->default(0);
            $table->bigInteger('outstanding_rupiah')->default(0);
            $table->bigInteger('surplus_rupiah')->default(0);

            $table->string('settlement_status', 32);

            $table->timestamp('created_at');
            $table->timestamp('updated_at')->nullable();

            $table->unique('note_revision_id', 'note_revision_settlements_revision_unique');
            $table->index('note_root_id', 'note_revision_settlements_root_idx');
            $table->index('settlement_status', 'note_revision_settlements_status_idx');
            $table->index(['note_root_id', 'settlement_status'], 'note_revision_settlements_root_status_idx');
            $table->index(['note_root_id', 'created_at'], 'note_revision_settlements_root_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('note_revision_settlements');
    }
};
