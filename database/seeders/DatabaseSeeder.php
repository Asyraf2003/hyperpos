<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ProductSeeder::class,        // 1. Master Produk
            SupplierSeeder::class,       // 2. Master Supplier
            SupplierInvoiceSeeder::class, // 3. Transaksi Faktur (Butuh 1 & 2)
        ]);
    }
}
