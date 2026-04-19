<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_events', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('bounded_context');
            $table->string('aggregate_type');
            $table->string('aggregate_id');
            $table->string('event_name');
            $table->string('actor_id')->nullable();
            $table->string('actor_role')->nullable();
            $table->text('reason')->nullable();
            $table->string('source_channel')->nullable();
            $table->string('request_id')->nullable();
            $table->string('correlation_id')->nullable();
            $table->dateTime('occurred_at');
            $table->json('metadata_json')->nullable();

            $table->index('event_name', 'audit_events_event_name_idx');
            $table->index('occurred_at', 'audit_events_occurred_at_idx');
            $table->index(['bounded_context', 'occurred_at'], 'audit_events_context_occurred_idx');
            $table->index(['aggregate_type', 'aggregate_id', 'occurred_at'], 'audit_events_aggregate_lookup_idx');
            $table->index(['actor_id', 'occurred_at'], 'audit_events_actor_lookup_idx');
        });

        Schema::create('audit_event_snapshots', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('audit_event_id');
            $table->string('snapshot_kind');
            $table->json('payload_json');
            $table->dateTime('created_at');

            $table->unique(['audit_event_id', 'snapshot_kind'], 'audit_event_snapshots_event_kind_unique');

            $table->foreign('audit_event_id', 'fk_audit_event_snapshots_event')
                ->references('id')
                ->on('audit_events')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('audit_event_snapshots', function (Blueprint $table): void {
            $table->dropForeign('fk_audit_event_snapshots_event');
        });

        Schema::dropIfExists('audit_event_snapshots');
        Schema::dropIfExists('audit_events');
    }
};
