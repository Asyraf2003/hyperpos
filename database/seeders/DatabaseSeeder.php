<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            ProductSeeder::class,
            SupplierSeeder::class,
            EmployeeFinanceSeeder::class,
            SupplierInvoiceSeeder::class,
            ExpenseSeeder::class,
            WorkshopStressTestSeeder::class,        
            SupplierPaymentProofSeeder::class,
        ]);
    }
}
