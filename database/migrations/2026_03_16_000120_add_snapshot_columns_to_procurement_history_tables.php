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
        Schema::table('supplier_invoices', function (Blueprint $table): void {
            $table->string('supplier_nama_pt_pengirim_snapshot')->nullable()->after('supplier_id');
        });

        Schema::table('supplier_invoice_lines', function (Blueprint $table): void {
            $table->string('product_kode_barang_snapshot')->nullable()->after('product_id');
            $table->string('product_nama_barang_snapshot')->nullable()->after('product_kode_barang_snapshot');
            $table->string('product_merek_snapshot')->nullable()->after('product_nama_barang_snapshot');
            $table->integer('product_ukuran_snapshot')->nullable()->after('product_merek_snapshot');
        });

        $invoiceRows = DB::table('supplier_invoices')
            ->join('suppliers', 'suppliers.id', '=', 'supplier_invoices.supplier_id')
            ->get([
                'supplier_invoices.id as supplier_invoice_id',
                'suppliers.nama_pt_pengirim',
            ]);

        foreach ($invoiceRows as $row) {
            DB::table('supplier_invoices')
                ->where('id', (string) $row->supplier_invoice_id)
                ->update([
                    'supplier_nama_pt_pengirim_snapshot' => (string) $row->nama_pt_pengirim,
                ]);
        }

        $lineRows = DB::table('supplier_invoice_lines')
            ->join('products', 'products.id', '=', 'supplier_invoice_lines.product_id')
            ->get([
                'supplier_invoice_lines.id as supplier_invoice_line_id',
                'products.kode_barang',
                'products.nama_barang',
                'products.merek',
                'products.ukuran',
            ]);

        foreach ($lineRows as $row) {
            DB::table('supplier_invoice_lines')
                ->where('id', (string) $row->supplier_invoice_line_id)
                ->update([
                    'product_kode_barang_snapshot' => $row->kode_barang !== null ? (string) $row->kode_barang : null,
                    'product_nama_barang_snapshot' => (string) $row->nama_barang,
                    'product_merek_snapshot' => (string) $row->merek,
                    'product_ukuran_snapshot' => $row->ukuran !== null ? (int) $row->ukuran : null,
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('supplier_invoice_lines', function (Blueprint $table): void {
            $table->dropColumn([
                'product_kode_barang_snapshot',
                'product_nama_barang_snapshot',
                'product_merek_snapshot',
                'product_ukuran_snapshot',
            ]);
        });

        Schema::table('supplier_invoices', function (Blueprint $table): void {
            $table->dropColumn([
                'supplier_nama_pt_pengirim_snapshot',
            ]);
        });
    }
};
