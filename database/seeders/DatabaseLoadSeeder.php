<?php

namespace Database\Seeders;

use Database\Seeders\Load\ProductLoadSeeder;
use Illuminate\Database\Seeder;

class DatabaseLoadSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DatabaseSeeder::class,
            ProductLoadSeeder::class,
            SupplierInvoiceAnnualDenseSeeder::class,
        ]);
    }
}
