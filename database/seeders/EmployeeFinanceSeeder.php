<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Faker\Factory;

final class EmployeeFinanceSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Factory::create('id_ID');
        
        // Ambil 20 ID karyawan yang baru saja dibuat atau buat baru
        for ($i = 0; $i < 20; $i++) {
            $employeeId = Str::uuid()->toString();
            $baseSalary = $faker->randomElement([3000000, 3500000, 4000000, 4500000, 5000000]);
            
            DB::table('employees')->insert([
                'id' => $employeeId,
                'name' => $faker->name,
                'phone' => '081' . $faker->numerify('#########'),
                'base_salary' => $baseSalary,
                'pay_period' => 'monthly',
                'status' => 'active',
                'created_at' => Carbon::now()->subYear(),
                'updated_at' => Carbon::now()->subYear(),
            ]);

            // Payroll 12 bulan
            for ($m = 1; $m <= 12; $m++) {
                $date = Carbon::now()->subMonths($m)->day(25);
                DB::table('payroll_disbursements')->insert([
                    'id' => Str::uuid()->toString(),
                    'employee_id' => $employeeId,
                    'amount' => $baseSalary,
                    'disbursement_date' => $date->format('Y-m-d H:i:s'),
                    'mode' => 'monthly',
                    'notes' => 'Gaji bulan ' . $date->format('F Y'),
                    'created_at' => $date,
                ]);
            }

            // Tambahkan Hutang untuk 10 orang pertama
            if ($i < 10) {
                $debtId = Str::uuid()->toString();
                $totalDebt = $faker->numberBetween(5, 15) * 100000; // 500k - 1.5jt
                $paidAmount = $faker->numberBetween(1, 4) * 100000;

                DB::table('employee_debts')->insert([
                    'id' => $debtId,
                    'employee_id' => $employeeId,
                    'total_debt' => $totalDebt,
                    'remaining_balance' => $totalDebt - $paidAmount,
                    'status' => 'unpaid',
                    'notes' => 'Pinjaman kasbon operasional',
                    'created_at' => Carbon::now()->subMonths(2),
                ]);

                DB::table('employee_debt_payments')->insert([
                    'id' => Str::uuid()->toString(),
                    'employee_debt_id' => $debtId,
                    'amount' => $paidAmount,
                    'payment_date' => Carbon::now()->subMonth()->format('Y-m-d H:i:s'),
                    'notes' => 'Cicilan via potong gaji',
                    'created_at' => Carbon::now()->subMonth(),
                ]);
            }
        }
    }
}
