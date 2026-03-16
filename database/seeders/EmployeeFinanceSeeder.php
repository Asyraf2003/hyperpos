<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

final class EmployeeFinanceSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        $employeeId = Str::uuid()->toString();

        // 1. Buat Karyawan Master
        DB::table('employees')->insert([
            'id' => $employeeId,
            'name' => 'Budi Teknisi',
            'phone' => '081234567890',
            'base_salary' => 3000000,
            'pay_period' => 'monthly',
            'status' => 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // 2. Buatkan Hutang Awal
        $debtId = Str::uuid()->toString();
        DB::table('employee_debts')->insert([
            'id' => $debtId,
            'employee_id' => $employeeId,
            'total_debt' => 500000,
            'remaining_balance' => 500000,
            'status' => 'unpaid',
            'notes' => 'Kasbon awal bulan',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
