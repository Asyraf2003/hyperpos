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
        Schema::table('products', function (Blueprint $table): void {
            $table->dropUnique('products_kode_barang_unique');
            $table->dropUnique('products_business_identity_unique');
        });

        DB::statement(
            "ALTER TABLE products
            ADD COLUMN active_unique_marker TINYINT(1)
            GENERATED ALWAYS AS (
                CASE
                    WHEN deleted_at IS NULL THEN 1
                    ELSE NULL
                END
            ) STORED
            AFTER delete_reason"
        );

        Schema::table('products', function (Blueprint $table): void {
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

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropUnique('products_kode_barang_active_unique');
            $table->dropUnique('products_business_identity_active_unique');
        });

        DB::statement(
            "ALTER TABLE products DROP COLUMN active_unique_marker"
        );

        Schema::table('products', function (Blueprint $table): void {
            $table->unique('kode_barang', 'products_kode_barang_unique');

            $table->unique(
                ['nama_barang_normalized', 'merek_normalized', 'ukuran'],
                'products_business_identity_unique'
            );
        });
    }
};
