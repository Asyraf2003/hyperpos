<?php

declare(strict_types=1);

namespace Database\Seeders;

use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class SupplierInvoiceBaselineSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = DB::table('suppliers')
            ->select('id', 'nama_pt_pengirim')
            ->orderBy('nama_pt_pengirim')
            ->get();

        $products = DB::table('products')
            ->select('id', 'kode_barang', 'nama_barang', 'merek', 'ukuran')
            ->whereNull('deleted_at')
            ->orderBy('nama_barang')
            ->get();

        if ($suppliers->count() < 5 || $products->count() < 8) {
            $this->command?->warn('SupplierInvoiceBaselineSeeder dilewati: butuh minimal 5 supplier dan 8 product aktif.');
            return;
        }

        $invoicePlan = [2, 3, 4, 2, 4, 3, 3];
        $startDate = CarbonImmutable::today()->subDays(6);

        $primarySuppliers = $suppliers->take(5)->values();
        $secondarySuppliers = $suppliers->slice(5)->values();

        $invoiceRunningNo = 1;

        foreach ($invoicePlan as $dayOffset => $invoiceCount) {
            $shipDate = $startDate->addDays($dayOffset);

            for ($n = 1; $n <= $invoiceCount; $n++) {
                $invoiceId = sprintf('seed-baseline-si-%03d', $invoiceRunningNo);
                $invoiceNo = sprintf('SI-BL-%s-%03d', $shipDate->format('Ymd'), $invoiceRunningNo);

                $supplier = $this->pickSupplier(
                    $invoiceRunningNo,
                    $primarySuppliers,
                    $secondarySuppliers
                );

                $lines = $this->buildLines(
                    invoiceId: $invoiceId,
                    invoiceRunningNo: $invoiceRunningNo,
                    products: $products,
                );

                DB::table('supplier_invoices')->updateOrInsert(
                    ['id' => $invoiceId],
                    [
                        'supplier_id' => (string) $supplier->id,
                        'supplier_nama_pt_pengirim_snapshot' => (string) $supplier->nama_pt_pengirim,
                        'nomor_faktur' => $invoiceNo,
                        'nomor_faktur_normalized' => mb_strtolower($invoiceNo, 'UTF-8'),
                        'document_kind' => 'invoice',
                        'lifecycle_status' => 'active',
                        'origin_supplier_invoice_id' => null,
                        'superseded_by_supplier_invoice_id' => null,
                        'tanggal_pengiriman' => $shipDate->format('Y-m-d'),
                        'jatuh_tempo' => $shipDate->addDays(30)->format('Y-m-d'),
                        'grand_total_rupiah' => array_sum(array_column($lines, 'line_total_rupiah')),
                        'voided_at' => null,
                        'void_reason' => null,
                        'last_revision_no' => 1,
                    ]
                );

                DB::table('supplier_invoice_lines')->where('supplier_invoice_id', $invoiceId)->delete();
                DB::table('supplier_invoice_lines')->insert($lines);

                $invoiceRunningNo++;
            }
        }

        $this->command?->info('SupplierInvoiceBaselineSeeder selesai: 21 faktur baseline 7 hari dibuat.');
    }

    private function pickSupplier(
        int $invoiceRunningNo,
        Collection $primarySuppliers,
        Collection $secondarySuppliers,
    ): object {
        if ($invoiceRunningNo % 4 !== 0 || $secondarySuppliers->isEmpty()) {
            return $primarySuppliers[($invoiceRunningNo - 1) % $primarySuppliers->count()];
        }

        return $secondarySuppliers[($invoiceRunningNo - 1) % $secondarySuppliers->count()];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function buildLines(
        string $invoiceId,
        int $invoiceRunningNo,
        Collection $products,
    ): array {
        $lineCount = 3 + (($invoiceRunningNo - 1) % 6);
        $lines = [];

        for ($lineNo = 1; $lineNo <= $lineCount; $lineNo++) {
            $productIndex = (($invoiceRunningNo * 7) + ($lineNo * 3)) % $products->count();
            $product = $products[$productIndex];
            $qty = 1 + (($invoiceRunningNo + ($lineNo * 2)) % 20);
            $unitCost = 18000 + ((($invoiceRunningNo * 6500) + ($lineNo * 3750)) % 280000);
            $lineTotal = $qty * $unitCost;

            $lines[] = [
                'id' => sprintf('%s-line-%02d', $invoiceId, $lineNo),
                'supplier_invoice_id' => $invoiceId,
                'revision_no' => 1,
                'is_current' => 1,
                'source_line_id' => null,
                'superseded_by_line_id' => null,
                'superseded_at' => null,
                'line_no' => $lineNo,
                'product_id' => (string) $product->id,
                'product_kode_barang_snapshot' => $product->kode_barang,
                'product_nama_barang_snapshot' => $product->nama_barang,
                'product_merek_snapshot' => $product->merek,
                'product_ukuran_snapshot' => $product->ukuran,
                'qty_pcs' => $qty,
                'line_total_rupiah' => $lineTotal,
                'unit_cost_rupiah' => $unitCost,
            ];
        }

        return $lines;
    }
}
