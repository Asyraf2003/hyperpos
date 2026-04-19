<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_versions', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('product_id');
            $table->unsignedInteger('revision_no');
            $table->string('event_name');
            $table->string('changed_by_actor_id')->nullable();
            $table->text('change_reason')->nullable();
            $table->dateTime('changed_at');
            $table->json('snapshot_json');

            $table->unique(['product_id', 'revision_no'], 'product_versions_product_revision_unique');
            $table->index(['product_id', 'changed_at'], 'product_versions_product_changed_at_idx');
            $table->index('event_name', 'product_versions_event_name_idx');

            $table->foreign('product_id', 'fk_product_versions_product')
                ->references('id')
                ->on('products')
                ->restrictOnDelete();
        });

        Schema::create('supplier_versions', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('supplier_id');
            $table->unsignedInteger('revision_no');
            $table->string('event_name');
            $table->string('changed_by_actor_id')->nullable();
            $table->text('change_reason')->nullable();
            $table->dateTime('changed_at');
            $table->json('snapshot_json');

            $table->unique(['supplier_id', 'revision_no'], 'supplier_versions_supplier_revision_unique');
            $table->index(['supplier_id', 'changed_at'], 'supplier_versions_supplier_changed_at_idx');
            $table->index('event_name', 'supplier_versions_event_name_idx');

            $table->foreign('supplier_id', 'fk_supplier_versions_supplier')
                ->references('id')
                ->on('suppliers')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('supplier_versions', function (Blueprint $table): void {
            $table->dropForeign('fk_supplier_versions_supplier');
        });

        Schema::table('product_versions', function (Blueprint $table): void {
            $table->dropForeign('fk_product_versions_product');
        });

        Schema::dropIfExists('supplier_versions');
        Schema::dropIfExists('product_versions');
    }
};
