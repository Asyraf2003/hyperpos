<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_invoice_versions', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('supplier_invoice_id');
            $table->unsignedInteger('revision_no');
            $table->string('event_name');
            $table->string('changed_by_actor_id')->nullable();
            $table->text('change_reason')->nullable();
            $table->timestamp('changed_at');
            $table->json('snapshot_json');

            $table->index('supplier_invoice_id');
            $table->index('event_name');
            $table->index('changed_at');
            $table->unique(['supplier_invoice_id', 'revision_no'], 'siv_supplier_invoice_revision_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_invoice_versions');
    }
};
