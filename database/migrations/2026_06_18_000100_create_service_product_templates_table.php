<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_product_templates', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('product_id');
            $table->string('service_catalog_item_id');
            $table->integer('default_service_price_rupiah');
            $table->integer('default_package_total_rupiah')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('product_id', 'service_product_templates_product_idx');
            $table->index('service_catalog_item_id', 'service_product_templates_service_catalog_item_idx');
            $table->index('is_active', 'service_product_templates_active_idx');
            $table->index(['product_id', 'is_active', 'sort_order'], 'service_product_templates_active_lookup_idx');

            $table->foreign('product_id', 'fk_service_product_templates_product')
                ->references('id')
                ->on('products')
                ->restrictOnDelete();

            $table->foreign('service_catalog_item_id', 'fk_service_product_templates_service_catalog_item')
                ->references('id')
                ->on('service_catalog_items')
                ->restrictOnDelete();
        });

        $this->addCheckConstraintsWhenSupported();
    }

    public function down(): void
    {
        Schema::dropIfExists('service_product_templates');
    }

    private function addCheckConstraintsWhenSupported(): void
    {
        $driver = DB::getDriverName();

        if (! in_array($driver, ['mysql', 'mariadb'], true)) {
            return;
        }

        DB::statement(
            'ALTER TABLE service_product_templates
             ADD CONSTRAINT service_product_templates_default_service_price_positive
             CHECK (default_service_price_rupiah > 0)'
        );

        DB::statement(
            'ALTER TABLE service_product_templates
             ADD CONSTRAINT service_product_templates_default_package_total_positive
             CHECK (default_package_total_rupiah IS NULL OR default_package_total_rupiah > 0)'
        );
    }
};
