<?php

declare(strict_types=1);

namespace Tests\Support;

use Illuminate\Support\Facades\DB;

trait SeedsProcurementDuplicateIsolationMatrixFixture
{
    private function seedSupplierAndProducts(): void
    {
        DB::table('suppliers')->insert([
            'id' => 'supplier-1',
            'nama_pt_pengirim' => 'PT Sumber Makmur',
            'nama_pt_pengirim_normalized' => 'pt sumber makmur',
        ]);

        DB::table('products')->insert([
            [
                'id' => 'product-1',
                'kode_barang' => 'KB-001',
                'nama_barang' => 'Ban Luar',
                'merek' => 'Federal',
                'ukuran' => 90,
                'harga_jual' => 35000,
            ],
            [
                'id' => 'product-2',
                'kode_barang' => 'KB-002',
                'nama_barang' => 'Ban Dalam',
                'merek' => 'Federal',
                'ukuran' => 90,
                'harga_jual' => 25000,
            ],
        ]);
    }

    private function seedInvoice(string $id, string $nomor, int $grand, string $productId = 'product-1', int $qty = 2, int $lineTotal = 20000, int $unitCost = 10000): void
    {
        $snapshots = [
            'product-1' => ['KB-001', 'Ban Luar'],
            'product-2' => ['KB-002', 'Ban Dalam'],
        ];

        [$kode, $nama] = $snapshots[$productId];

        DB::table('supplier_invoices')->insert([
            'id' => $id,
            'supplier_id' => 'supplier-1',
            'supplier_nama_pt_pengirim_snapshot' => 'PT Sumber Makmur',
            'nomor_faktur' => $nomor,
            'nomor_faktur_normalized' => strtolower($nomor),
            'document_kind' => 'invoice',
            'lifecycle_status' => 'active',
            'origin_supplier_invoice_id' => null,
            'superseded_by_supplier_invoice_id' => null,
            'tanggal_pengiriman' => '2026-03-15',
            'jatuh_tempo' => '2026-04-14',
            'grand_total_rupiah' => $grand,
            'voided_at' => null,
            'void_reason' => null,
            'last_revision_no' => 1,
        ]);

        DB::table('supplier_invoice_lines')->insert([
            'id' => $id . '-line-1',
            'supplier_invoice_id' => $id,
            'revision_no' => 1,
            'is_current' => 1,
            'source_line_id' => null,
            'superseded_by_line_id' => null,
            'superseded_at' => null,
            'line_no' => 1,
            'product_id' => $productId,
            'product_kode_barang_snapshot' => $kode,
            'product_nama_barang_snapshot' => $nama,
            'product_merek_snapshot' => 'Federal',
            'product_ukuran_snapshot' => 90,
            'qty_pcs' => $qty,
            'line_total_rupiah' => $lineTotal,
            'unit_cost_rupiah' => $unitCost,
        ]);
    }
}
