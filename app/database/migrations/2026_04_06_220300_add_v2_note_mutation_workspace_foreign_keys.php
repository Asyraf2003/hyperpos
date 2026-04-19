<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('note_mutation_events', function (Blueprint $table): void {
            $table->index('related_customer_payment_id', 'nme_related_payment_idx');
            $table->index('related_customer_refund_id', 'nme_related_refund_idx');

            $table->foreign('note_id', 'fk_nme_note')
                ->references('id')
                ->on('notes')
                ->restrictOnDelete();

            $table->foreign('related_customer_payment_id', 'fk_nme_rel_payment')
                ->references('id')
                ->on('customer_payments')
                ->restrictOnDelete();

            $table->foreign('related_customer_refund_id', 'fk_nme_rel_refund')
                ->references('id')
                ->on('customer_refunds')
                ->restrictOnDelete();
        });

        Schema::table('note_mutation_snapshots', function (Blueprint $table): void {
            $table->foreign('note_mutation_event_id', 'fk_nms_event')
                ->references('id')
                ->on('note_mutation_events')
                ->restrictOnDelete();
        });

        Schema::table('transaction_workspace_drafts', function (Blueprint $table): void {
            $table->foreign('note_id', 'fk_twd_note')
                ->references('id')
                ->on('notes')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('transaction_workspace_drafts', function (Blueprint $table): void {
            $table->dropForeign('fk_twd_note');
        });

        Schema::table('note_mutation_snapshots', function (Blueprint $table): void {
            $table->dropForeign('fk_nms_event');
        });

        Schema::table('note_mutation_events', function (Blueprint $table): void {
            $table->dropForeign('fk_nme_rel_refund');
            $table->dropForeign('fk_nme_rel_payment');
            $table->dropForeign('fk_nme_note');

            $table->dropIndex('nme_related_refund_idx');
            $table->dropIndex('nme_related_payment_idx');
        });
    }
};
