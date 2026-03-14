<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_item_store_stock_lines', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('work_item_id');
            $table->string('product_id');
            $table->integer('qty');
            $table->integer('line_total_rupiah');

            $table->index('work_item_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_item_store_stock_lines');
    }
};
