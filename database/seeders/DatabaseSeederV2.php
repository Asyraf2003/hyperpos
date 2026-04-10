<?php

namespace Database\Seeders;

use Database\Seeders\Product\ProductVolumeSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeederV2 extends Seeder
{
    public function run(): void
    {
        $this->call([
            DatabaseSeeder::class,
            ProductVolumeSeeder::class,
            SupplierInvoiceAnnualDenseSeeder::class,
        ]);
    }
}
