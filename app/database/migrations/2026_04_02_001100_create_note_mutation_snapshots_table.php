<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('note_mutation_snapshots', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('note_mutation_event_id');
            $table->string('snapshot_kind');
            $table->longText('payload_json');
            $table->dateTime('created_at');

            $table->index('note_mutation_event_id', 'nms_event_idx');
            $table->index('snapshot_kind');
            $table->unique(['note_mutation_event_id', 'snapshot_kind'], 'note_mutation_snapshots_unique_event_kind');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('note_mutation_snapshots');
    }
};
