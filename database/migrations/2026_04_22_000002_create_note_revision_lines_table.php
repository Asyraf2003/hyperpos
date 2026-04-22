<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('note_revision_lines', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('note_revision_id');
            $table->string('work_item_root_id')->nullable();

            $table->unsignedInteger('line_no');
            $table->string('transaction_type');
            $table->string('status');

            $table->string('service_label')->nullable();
            $table->unsignedBigInteger('service_price_rupiah')->nullable();
            $table->unsignedBigInteger('subtotal_rupiah')->default(0);

            $table->json('payload')->nullable();

            $table->timestamp('created_at');
            $table->timestamp('updated_at')->nullable();

            $table->index('note_revision_id', 'note_revision_lines_revision_idx');
            $table->index('work_item_root_id', 'note_revision_lines_work_item_root_idx');
            $table->unique(['note_revision_id', 'line_no'], 'note_revision_lines_revision_line_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('note_revision_lines');
    }
};
