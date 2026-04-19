<?php

declare(strict_types=1);

namespace Database\Seeders;

use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class SupplierInvoiceVoidedScenarioSeeder extends Seeder
{
    public function run(): void
    {
        $supplier = DB::table('suppliers')
            ->select('id', 'nama_pt_pengirim')
            ->orderBy('nama_pt_pengirim')
            ->first();

        $products = DB::table('products')
            ->select('id', 'kode_barang', 'nama_barang', 'merek', 'ukuran')
            ->whereNull('deleted_at')
            ->orderBy('nama_barang')
            ->limit(3)
            ->get()
            ->values();

        if ($supplier === null || $products->count() < 2) {
            $this->command?->warn('SupplierInvoiceVoidedScenarioSeeder dilewati: butuh minimal 1 supplier dan 2 product aktif.');
            return;
        }

        $this->seedVoidedInvoice(
            invoiceId: 'seed-si-voided-basic',
            supplier: $supplier,
            lineDefs: [
                ['product' => $products[0], 'qty' => 2, 'unit_cost' => 12500],
                ['product' => $products[1], 'qty' => 1, 'unit_cost' => 18000],
            ],
            nomorFaktur: 'SI-VOID-001',
            shipDate: '2026-03-09',
            voidedAt: '2026-03-09 10:15:00',
            voidReason: 'Salah input sebelum ada efek domain.',
        );

        $this->seedVoidedInvoice(
            invoiceId: 'seed-si-voided-reuse-source',
            supplier: $supplier,
            lineDefs: [
                ['product' => $products[0], 'qty' => 1, 'unit_cost' => 14000],
                ['product' => $products[1], 'qty' => 2, 'unit_cost' => 16000],
            ],
            nomorFaktur: 'SI-VOID-REUSE-001',
            shipDate: '2026-03-10',
            voidedAt: '2026-03-10 09:00:00',
            voidReason: 'Nomor faktur lama dibatalkan sebelum dipakai ulang.',
        );

        $this->seedActiveEditableInvoice(
            invoiceId: 'seed-si-voided-reuse-active',
            supplier: $supplier,
            lineDefs: [
                ['product' => $products[0], 'qty' => 3, 'unit_cost' => 15000],
                ['product' => $products[1], 'qty' => 1, 'unit_cost' => 20000],
            ],
            nomorFaktur: 'SI-VOID-REUSE-001',
            shipDate: '2026-03-11',
        );

        $this->command?->info('SupplierInvoiceVoidedScenarioSeeder selesai: skenario nota void level 2 dibuat.');
    }

    private function seedVoidedInvoice(
        string $invoiceId,
        object $supplier,
        array $lineDefs,
        string $nomorFaktur,
        string $shipDate,
        string $voidedAt,
        string $voidReason,
    ): void {
        DB::table('supplier_invoice_lines')->where('supplier_invoice_id', $invoiceId)->delete();
        DB::table('supplier_invoices')->where('id', $invoiceId)->delete();

        $lines = $this->buildLines($invoiceId, $lineDefs);
        $grandTotal = array_sum(array_column($lines, 'line_total_rupiah'));

        DB::table('supplier_invoices')->insert([
            'id' => $invoiceId,
            'supplier_id' => (string) $supplier->id,
            'supplier_nama_pt_pengirim_snapshot' => (string) $supplier->nama_pt_pengirim,
            'nomor_faktur' => $nomorFaktur,
            'nomor_faktur_normalized' => mb_strtolower($nomorFaktur, 'UTF-8'),
            'document_kind' => 'invoice',
            'lifecycle_status' => 'active',
            'origin_supplier_invoice_id' => null,
            'superseded_by_supplier_invoice_id' => null,
            'tanggal_pengiriman' => $shipDate,
            'jatuh_tempo' => CarbonImmutable::parse($shipDate)->addDays(30)->format('Y-m-d'),
            'grand_total_rupiah' => $grandTotal,
            'voided_at' => $voidedAt,
            'void_reason' => $voidReason,
            'last_revision_no' => 1,
        ]);

        DB::table('supplier_invoice_lines')->insert($lines);
    }

    private function seedActiveEditableInvoice(
        string $invoiceId,
        object $supplier,
        array $lineDefs,
        string $nomorFaktur,
        string $shipDate,
    ): void {
        DB::table('supplier_invoice_lines')->where('supplier_invoice_id', $invoiceId)->delete();
        DB::table('supplier_invoices')->where('id', $invoiceId)->delete();

        $lines = $this->buildLines($invoiceId, $lineDefs);
        $grandTotal = array_sum(array_column($lines, 'line_total_rupiah'));

        DB::table('supplier_invoices')->insert([
            'id' => $invoiceId,
            'supplier_id' => (string) $supplier->id,
            'supplier_nama_pt_pengirim_snapshot' => (string) $supplier->nama_pt_pengirim,
            'nomor_faktur' => $nomorFaktur,
            'nomor_faktur_normalized' => mb_strtolower($nomorFaktur, 'UTF-8'),
            'document_kind' => 'invoice',
            'lifecycle_status' => 'active',
            'origin_supplier_invoice_id' => null,
            'superseded_by_supplier_invoice_id' => null,
            'tanggal_pengiriman' => $shipDate,
            'jatuh_tempo' => CarbonImmutable::parse($shipDate)->addDays(30)->format('Y-m-d'),
            'grand_total_rupiah' => $grandTotal,
            'voided_at' => null,
            'void_reason' => null,
            'last_revision_no' => 1,
        ]);

        DB::table('supplier_invoice_lines')->insert($lines);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function buildLines(string $invoiceId, array $lineDefs): array
    {
        $lines = [];

        foreach ($lineDefs as $index => $def) {
            $lineTotal = (int) $def['qty'] * (int) $def['unit_cost'];

            $lines[] = [
                'id' => $invoiceId . '-line-' . ($index + 1),
                'supplier_invoice_id' => $invoiceId,
                'revision_no' => 1,
                'is_current' => 1,
                'source_line_id' => null,
                'superseded_by_line_id' => null,
                'superseded_at' => null,
                'line_no' => $index + 1,
                'product_id' => (string) $def['product']->id,
                'product_kode_barang_snapshot' => $def['product']->kode_barang,
                'product_nama_barang_snapshot' => $def['product']->nama_barang,
                'product_merek_snapshot' => $def['product']->merek,
                'product_ukuran_snapshot' => $def['product']->ukuran,
                'qty_pcs' => (int) $def['qty'],
                'line_total_rupiah' => $lineTotal,
                'unit_cost_rupiah' => (int) $def['unit_cost'],
            ];
        }

        return $lines;
    }
}
