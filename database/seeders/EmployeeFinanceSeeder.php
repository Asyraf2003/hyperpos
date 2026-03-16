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

            for ($month = 1; $month <= 12; $month++) {
                $disburseDate = Carbon::now()->subMonths($month)->day(25);
                DB::table('payroll_disbursements')->insert([
                    'id' => Str::uuid()->toString(),
                    'employee_id' => $employeeId,
                    'amount' => $baseSalary,
                    'disbursement_date' => $disburseDate->format('Y-m-d H:i:s'),
                    'mode' => 'monthly',
                    'notes' => 'Gaji bulan ' . $disburseDate->format('F Y'),
                    'created_at' => $disburseDate,
                    'updated_at' => $disburseDate,
                ]);
            }
        }
    }
}
