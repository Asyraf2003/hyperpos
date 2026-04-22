<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('note_revisions', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('note_root_id');
            $table->unsignedInteger('revision_number');
            $table->string('parent_revision_id')->nullable();
            $table->string('created_by_actor_id')->nullable();
            $table->text('reason')->nullable();

            $table->string('customer_name');
            $table->string('customer_phone')->nullable();
            $table->date('transaction_date');

            $table->unsignedBigInteger('grand_total_rupiah')->default(0);
            $table->unsignedInteger('line_count')->default(0);

            $table->timestamp('created_at');
            $table->timestamp('updated_at')->nullable();

            $table->unique(['note_root_id', 'revision_number'], 'note_revisions_root_revision_unique');
            $table->index('note_root_id', 'note_revisions_root_idx');
            $table->index('parent_revision_id', 'note_revisions_parent_idx');
            $table->index('created_by_actor_id', 'note_revisions_actor_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('note_revisions');
    }
};
