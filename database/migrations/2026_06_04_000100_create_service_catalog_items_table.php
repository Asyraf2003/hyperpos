<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_catalog_items', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('name');
            $table->string('normalized_name')->unique('service_catalog_items_normalized_name_unique');
            $table->integer('default_price_rupiah');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_catalog_items');
    }
};
