<?php

declare(strict_types=1);

namespace Tests\Feature\EmployeeFinance;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

final class EmployeeDetailPageFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_accessing_employee_detail_page(): void
    {
        $this->get(route('admin.employees.show', ['employeeId' => 'employee-1']))
            ->assertRedirect(route('login'));
    }

    public function test_kasir_is_redirected_back_to_cashier_dashboard_when_accessing_employee_detail_page(): void
    {
        $response = $this->actingAs($this->createUserWithRole('kasir-employee-detail@example.test', 'kasir'))
            ->get(route('admin.employees.show', ['employeeId' => 'employee-1']));

        $response->assertRedirect(route('cashier.dashboard'));
        $response->assertSessionHas('error', 'Halaman admin hanya untuk role admin.');
    }

    public function test_admin_can_access_employee_detail_page(): void
    {
        $employeeId = $this->seedEmployee();

        $response = $this->actingAs($this->createUserWithRole('admin-employee-detail@example.test', 'admin'))
            ->get(route('admin.employees.show', ['employeeId' => $employeeId]));

        $response->assertOk();
        $response->assertSee('Detail Karyawan');
        $response->assertSee('Ringkasan Karyawan');
        $response->assertSee('Budi Santoso');
        $response->assertSee('Mingguan');
        $response->assertSee('Aktif');
        $response->assertSee('Ringkasan Hutang Karyawan');
        $response->assertSee('Riwayat Hutang');
        $response->assertSee('Riwayat Pembayaran Hutang');
        $response->assertSee('Ringkasan Riwayat Gaji');
        $response->assertSee('Riwayat Gaji');
        $response->assertSee('Edit Karyawan');
    }

    public function test_admin_can_see_employee_debt_summary_and_histories_on_detail_page(): void
    {
        $employeeId = $this->seedEmployee();
        $debtId = (string) Str::uuid();

        DB::table('employee_debts')->insert([
            'id' => $debtId,
            'employee_id' => $employeeId,
            'total_debt' => 1000000,
            'remaining_balance' => 250000,
            'status' => 'unpaid',
            'notes' => 'Pinjaman kebutuhan keluarga',
            'created_at' => now()->subDays(5),
            'updated_at' => now()->subDays(5),
        ]);

        DB::table('employee_debt_payments')->insert([
            'id' => (string) Str::uuid(),
            'employee_debt_id' => $debtId,
            'amount' => 750000,
            'payment_date' => now()->subDay(),
            'notes' => 'Potong gaji minggu ini',
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($this->createUserWithRole('admin-employee-detail-debt@example.test', 'admin'))
            ->get(route('admin.employees.show', ['employeeId' => $employeeId]));

        $response->assertOk();
        $response->assertSee('Total Record Hutang');
        $response->assertSee('Rp1.000.000');
        $response->assertSee('Rp250.000');
        $response->assertSee('1 aktif');
        $response->assertSee('0 lunas');
        $response->assertSee('Pinjaman kebutuhan keluarga');
        $response->assertSee('Belum Lunas');
        $response->assertSee('Potong gaji minggu ini');
        $response->assertSee('Buka Hutang');
    }

    public function test_admin_can_see_employee_payroll_summary_on_detail_page_and_payroll_rows_from_json_table_endpoint(): void
    {
        $employeeId = $this->seedEmployee();

        DB::table('payroll_disbursements')->insert([
            [
                'id' => (string) Str::uuid(),
                'employee_id' => $employeeId,
                'amount' => 1500000,
                'disbursement_date' => '2026-03-20 08:00:00',
                'mode' => 'weekly',
                'notes' => 'Gaji minggu ke-3',
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ],
            [
                'id' => (string) Str::uuid(),
                'employee_id' => $employeeId,
                'amount' => 1500000,
                'disbursement_date' => '2026-03-27 08:00:00',
                'mode' => 'weekly',
                'notes' => 'Gaji minggu ke-4',
                'created_at' => now()->subDay(),
                'updated_at' => now()->subDay(),
            ],
        ]);

        $admin = $this->createUserWithRole('admin-employee-detail-payroll@example.test', 'admin');

        $response = $this->actingAs($admin)
            ->get(route('admin.employees.show', ['employeeId' => $employeeId]));

        $response->assertOk();
        $response->assertSee('Total Record Payroll');
        $response->assertSee('2');
        $response->assertSee('Rp3.000.000');
        $response->assertSee('2026-03-27');
        $response->assertSee('Riwayat Gaji');
        $response->assertSee('employee-payroll-table-body', false);
        $response->assertSee('employee-payroll-table-summary', false);
        $response->assertSee('employee-payroll-table-pagination', false);
        $response->assertSee('admin-employee-payroll-table.js');
        $response->assertSee(json_encode(route('admin.employees.payroll-table', ['employeeId' => $employeeId])), false);

        $tableResponse = $this->actingAs($admin)->getJson(route('admin.employees.payroll-table', [
            'employeeId' => $employeeId,
            'page' => 1,
            'per_page' => 10,
        ]));

        $tableResponse->assertOk();
        $tableResponse->assertJsonPath('success', true);
        $tableResponse->assertJsonPath('data.meta.page', 1);
        $tableResponse->assertJsonPath('data.meta.per_page', 10);
        $tableResponse->assertJsonPath('data.meta.total', 2);
        $tableResponse->assertJsonPath('data.meta.last_page', 1);
        $tableResponse->assertJsonFragment(['notes' => 'Gaji minggu ke-3']);
        $tableResponse->assertJsonFragment(['notes' => 'Gaji minggu ke-4']);
        $tableResponse->assertJsonFragment(['mode_label' => 'Mingguan']);
    }

    public function test_admin_is_redirected_to_index_when_employee_detail_is_missing(): void
    {
        $response = $this->actingAs($this->createUserWithRole('admin-employee-detail-missing@example.test', 'admin'))
            ->get(route('admin.employees.show', ['employeeId' => 'missing-employee']));

        $response->assertRedirect(route('admin.employees.index'));
        $response->assertSessionHas('error', 'Data karyawan tidak ditemukan.');
    }

    private function seedEmployee(): string
    {
        $employeeId = (string) Str::uuid();

        DB::table('employees')->insert([
            'id' => $employeeId,
            'name' => 'Budi Santoso',
            'phone' => '081211111111',
            'base_salary' => 5000000,
            'pay_period' => 'weekly',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $employeeId;
    }

    private function createUserWithRole(string $email, string $role): User
    {
        $user = User::query()->create([
            'name' => 'Test User',
            'email' => $email,
            'password' => 'password123',
        ]);

        DB::table('actor_accesses')->insert([
            'actor_id' => (string) $user->getAuthIdentifier(),
            'role' => $role,
        ]);

        return $user;
    }
}
