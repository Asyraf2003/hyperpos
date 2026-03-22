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
        $products = DB::table('products')->get(); 
        
        if ($products->isEmpty()) {
            return;
        }

        // Daftar disamakan persis dengan SupplierSeeder
        $suppliers = [
            'PT. Astra Otoparts (Clutch Div)', 'PT. FCC Indonesia', 'PT. Exedy Manufacturing Indonesia',
            'PT. Faito Racing Indonesia', 'PT. TDR Industries', 'PT. Kawahara Racing',
            'PT. Bintang Racing Team (BRT)', 'PT. Chemco Harapan Nusantara', 'PT. Dirgaputra Eta Sembada',
            'PT. Daido Indonesia Manufacturing', 'PT. Federal Izumi Manufacturing', 'PT. Showa Indonesia Manufacturing',
            'PT. Musashi Auto Parts Indonesia', 'PT. Nissin Kogyo Indonesia', 'PT. Akebono Brake Astra Indonesia',
            'PT. TPR Indonesia', 'PT. Mikuni Indonesia', 'PT. Keihin Indonesia',
            'PT. Denso Indonesia', 'PT. Yamaha Indonesia Motor Mfg (Parts)'
        ];

        for ($i = 0; $i < 15; $i++) {
            $selectedProducts = $products->random(rand(2, 4));
            $lines = [];

            foreach ($selectedProducts as $p) {
                $qty = rand(10, 30); 
                $hargaModalSatuan = (int) ($p->harga_jual * 0.8);
                $lineTotal = $qty * $hargaModalSatuan;

                $lines[] = [
                    'product_id' => $p->id,
                    'qty_pcs' => $qty,
                    'line_total_rupiah' => $lineTotal,
                ];
            }

            $result = $handler->handle(
                pt: $suppliers[array_rand($suppliers)],
                tgl: now()->subDays(rand(1, 15))->format('Y-m-d'),
                lines: $lines
            );

            if ($result->isFailure()) {
                Log::error("Gagal Seed Invoice: " . $result->message(), $result->errors());
            }
        }
    }
}
