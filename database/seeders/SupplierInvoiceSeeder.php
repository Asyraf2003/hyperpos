<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Application\Procurement\UseCases\CreateSupplierInvoiceHandler;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class SupplierInvoiceSeeder extends Seeder
{
    public function run(CreateSupplierInvoiceHandler $handler): void
    {
        // 1. Ambil semua product langsung dari tabel (Infrastructure way)
        $products = DB::table('products')->get(); 
        
        if ($products->isEmpty()) {
            return;
        }

        // Gunakan nama yang sudah ada di SupplierSeeder agar resolve() menemukannya
        $suppliers = ['PT. Astra Otoparts', 'PT. KYB Indonesia', 'CV. Motor Jaya Mandiri'];

        // 2. Buat 5 Faktur Random
        for ($i = 0; $i < 5; $i++) {
            $selectedProducts = $products->random(rand(2, 4));
            $lines = [];

            foreach ($selectedProducts as $p) {
                $qty = rand(10, 20); // Sedikit dikurangi agar angka total tidak terlalu fantastis
                $hargaModalSatuan = (int) ($p->harga_jual * 0.8);
                
                // Factory Anda minta line_total_rupiah (Total per baris)
                $lineTotal = $qty * $hargaModalSatuan;

                $lines[] = [
                    'product_id' => $p->id,
                    'qty_pcs' => $qty, // Sesuai SupplierInvoiceFactory.php
                    'line_total_rupiah' => $lineTotal, // Sesuai SupplierInvoiceFactory.php
                ];
            }

            $result = $handler->handle(
                pt: $suppliers[array_rand($suppliers)],
                tgl: now()->subDays(rand(1, 10))->format('Y-m-d'),
                lines: $lines
            );

            if ($result->isFailure()) {
                Log::error("Gagal Seed Invoice: " . $result->message(), $result->errors());
            }
        }
    }
}
