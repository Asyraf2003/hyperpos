<?php

declare(strict_types=1);

namespace Tests\Feature\EmployeeFinance;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

final class EmployeeDebtDetailPageFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_employee_debt_detail_page_and_see_payment_history(): void
    {
        $employeeId = (string) Str::uuid();
        $debtId = (string) Str::uuid();

        DB::table('employees')->insert([
            'id' => $employeeId,
            'employee_name' => 'Asyraf Detail Hutang',
            'phone' => '081111111111',
            'default_salary_amount' => 5000000,
            'salary_basis_type' => 'monthly',
            'employment_status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('employee_debts')->insert([
            'id' => $debtId,
            'employee_id' => $employeeId,
            'total_debt' => 1000000,
            'remaining_balance' => 750000,
            'status' => 'unpaid',
            'notes' => 'Kasbon awal',
            'created_at' => '2026-03-22 10:00:00',
            'updated_at' => now(),
        ]);

        DB::table('employee_debt_payments')->insert([
            'id' => (string) Str::uuid(),
            'employee_debt_id' => $debtId,
            'amount' => 250000,
            'payment_date' => '2026-03-23 09:30:00',
            'notes' => 'Cicilan pertama',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($this->createUserWithRole('admin-debt-detail@example.test', 'admin'))
            ->get(route('admin.employee-debts.show', ['debtId' => $debtId]));

        $response->assertOk();
        $response->assertSee('Ringkasan Hutang');
        $response->assertSee('Asyraf Detail Hutang');
        $response->assertSee('Rp1.000.000');
        $response->assertSee('Rp250.000');
        $response->assertSee('Rp750.000');
        $response->assertSee('Cicilan pertama');
    }

    public function test_admin_can_store_employee_debt_payment_from_detail_page(): void
    {
        $employeeId = (string) Str::uuid();
        $debtId = (string) Str::uuid();

        DB::table('employees')->insert([
            'id' => $employeeId,
            'employee_name' => 'Asyraf Bayar Hutang',
            'phone' => '081111111111',
            'default_salary_amount' => 5000000,
            'salary_basis_type' => 'monthly',
            'employment_status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('employee_debts')->insert([
            'id' => $debtId,
            'employee_id' => $employeeId,
            'total_debt' => 1000000,
            'remaining_balance' => 1000000,
            'status' => 'unpaid',
            'notes' => 'Kasbon awal',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($this->createUserWithRole('admin-debt-payment@example.test', 'admin'))
            ->post(route('admin.employee-debts.payments.store', ['debtId' => $debtId]), [
                'payment_amount' => 250000,
                'notes' => 'Cicilan pertama',
            ]);

        $response->assertRedirect(route('admin.employee-debts.show', ['debtId' => $debtId]));
        $response->assertSessionHas('success', 'Pembayaran hutang berhasil dicatat.');

        $this->assertDatabaseHas('employee_debt_payments', [
            'employee_debt_id' => $debtId,
            'amount' => 250000,
            'notes' => 'Cicilan pertama',
        ]);

        $this->assertDatabaseHas('employee_debts', [
            'id' => $debtId,
            'remaining_balance' => 750000,
            'status' => 'unpaid',
        ]);
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
