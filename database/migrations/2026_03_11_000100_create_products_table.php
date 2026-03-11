<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('kode_barang')->nullable();
            $table->string('nama_barang');
            $table->string('merek');
            $table->integer('ukuran')->nullable();
            $table->integer('harga_jual');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
