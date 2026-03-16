<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Application\Procurement\UseCases\CreateSupplierInvoiceHandler;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class SupplierInvoiceSeeder extends Seeder
{
    /**
     * Kita tidak perlu ProductReaderPort di sini, 
     * langsung gunakan DB Facade karena ini level Infrastructure/Seeder.
     */
    public function run(CreateSupplierInvoiceHandler $handler): void
    {
        // 1. Ambil semua product langsung dari tabel
        $products = DB::table('products')->get(); 
        
        if ($products->isEmpty()) {
            return;
        }

        $suppliers = ['PT. Astra Otoparts', 'PT. KYB Indonesia', 'CV. Motor Jaya Mandiri'];

        // 2. Buat 5 Faktur Random
        for ($i = 0; $i < 5; $i++) {
            // Ambil 2-4 barang secara acak
            $selectedProducts = $products->random(rand(2, 4));
            $lines = [];

            foreach ($selectedProducts as $p) {
                $qty = rand(30, 50);
                
                // Akses properti kolom DB (asumsi nama kolom adalah harga_jual)
                $hargaModal = (int) ($p->harga_jual * 0.8);

                $lines[] = [
                    'product_id' => $p->id, // Menggunakan properti id, bukan method id()
                    'quantity' => $qty,
                    'price' => $hargaModal, 
                ];
            }

            $handler->handle(
                pt: $suppliers[array_rand($suppliers)],
                tgl: now()->subDays(rand(1, 10))->format('Y-m-d'),
                lines: $lines
            );
        }
    }
}
