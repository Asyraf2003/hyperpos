<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

final class ExpenseSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // 1. Definisikan Kategori yang Relevan untuk Operasional Bengkel
        $categoriesData = [
            ['code' => 'EXP-ELEC', 'name' => 'Listrik Bengkel', 'desc' => 'Token atau tagihan listrik'],
            ['code' => 'EXP-WTR', 'name' => 'Air PDAM', 'desc' => 'Tagihan air bengkel'],
            ['code' => 'EXP-SNCK', 'name' => 'Konsumsi Harian', 'desc' => 'Makan siang, kopi, rokok mekanik'],
            ['code' => 'EXP-FUEL', 'name' => 'Bensin Operasional', 'desc' => 'Bensin pikap / kendaraan operasional'],
            ['code' => 'EXP-ATK', 'name' => 'ATK & Kebersihan', 'desc' => 'Nota, pulpen, sabun cuci tangan, kain lap'],
            ['code' => 'EXP-MISC', 'name' => 'Lain-lain', 'desc' => 'Pengeluaran tak terduga (iuran RT, sampah)'],
        ];

        $categories = [];
        $categoryInserts = [];

        foreach ($categoriesData as $cat) {
            $id = Str::uuid()->toString();
            $categories[] = [
                'id' => $id,
                'name' => $cat['name'],
                'code' => $cat['code'] // Simpan code untuk logika nilai pengeluaran nanti
            ];
            
            $categoryInserts[] = [
                'id' => $id,
                'code' => $cat['code'],
                'name' => $cat['name'],
                'description' => $cat['desc'],
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('expense_categories')->insert($categoryInserts);

        // 2. Generate Data Pengeluaran Ekstrim (1 Tahun Penuh)
        $expenses = [];
        $paymentMethods = ['cash', 'cash', 'cash', 'transfer']; // Perbanyak probabilitas cash
        $statuses = ['paid', 'paid', 'paid', 'paid', 'pending']; // Mayoritas sudah lunas

        $startDate = Carbon::now()->subYear();
        $endDate = Carbon::now();

        $this->command->info('Membuat ribuan data pengeluaran operasional...');

        while ($startDate->lte($endDate)) {
            // Bengkel buka, ada 1 s/d 5 pengeluaran per hari
            $dailyTransactions = rand(1, 5);

            for ($i = 0; $i < $dailyTransactions; $i++) {
                $category = $categories[array_rand($categories)];
                
                // Logika harga realistis berdasarkan kategori
                $amountRupiah = match($category['code']) {
                    'EXP-ELEC' => rand(20, 100) * 10000,   // 200k - 1jt (biasanya bulanan, tapi disimulasikan beli token)
                    'EXP-SNCK' => rand(3, 15) * 10000,     // 30k - 150k per hari
                    'EXP-FUEL' => rand(5, 15) * 10000,     // 50k - 150k
                    'EXP-ATK'  => rand(2, 8) * 10000,      // 20k - 80k
                    default    => rand(5, 20) * 10000,
                };

                $expenses[] = [
                    'id' => Str::uuid()->toString(),
                    'category_id' => $category['id'],
                    'amount_rupiah' => $amountRupiah,
                    'expense_date' => $startDate->format('Y-m-d'),
                    'description' => 'Pembayaran ' . $category['name'],
                    'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                    'reference_no' => 'REF-' . $startDate->format('Ymd') . '-' . Str::upper(Str::random(4)),
                    'status' => $statuses[array_rand($statuses)],
                    'created_at' => $startDate->copy()->addHours(rand(8, 17)), // Jam acak waktu bengkel buka
                    'updated_at' => $startDate->copy()->addHours(rand(8, 17)),
                ];
            }

            $startDate->addDay();
        }

        // 3. Insert dalam skema Chunk untuk mencegah memory / query limit error
        $chunks = array_chunk($expenses, 500);
        foreach ($chunks as $chunk) {
            DB::table('operational_expenses')->insert($chunk);
        }

        $this->command->info('Berhasil menanamkan ' . count($expenses) . ' baris pengeluaran operasional!');
    }
}
