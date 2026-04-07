<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropUnique('products_kode_barang_active_unique');
            $table->dropUnique('products_business_identity_active_unique');

            $table->unique(
                ['kode_barang', 'active_unique_marker'],
                'products_kode_barang_unique'
            );

            $table->unique(
                ['nama_barang_normalized', 'merek_normalized', 'ukuran', 'active_unique_marker'],
                'products_business_identity_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropUnique('products_kode_barang_unique');
            $table->dropUnique('products_business_identity_unique');

            $table->unique(
                ['kode_barang', 'active_unique_marker'],
                'products_kode_barang_active_unique'
            );

            $table->unique(
                ['nama_barang_normalized', 'merek_normalized', 'ukuran', 'active_unique_marker'],
                'products_business_identity_active_unique'
            );
        });
    }
};
