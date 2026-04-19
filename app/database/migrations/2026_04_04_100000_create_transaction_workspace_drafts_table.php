<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaction_workspace_drafts', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('actor_id');
            $table->string('workspace_mode');
            $table->string('workspace_key');
            $table->string('note_id')->nullable();
            $table->longText('payload_json');
            $table->dateTime('created_at');
            $table->dateTime('updated_at');

            $table->index('actor_id');
            $table->index('workspace_mode');
            $table->index('note_id');
            $table->unique(['actor_id', 'workspace_key'], 'twd_actor_workspace_key_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_workspace_drafts');
    }
};
