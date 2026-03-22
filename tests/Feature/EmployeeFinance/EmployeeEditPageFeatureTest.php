<?php

declare(strict_types=1);

namespace Tests\Feature\EmployeeFinance;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

final class EmployeeEditPageFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_accessing_edit_employee_page(): void
    {
        $this->get(route('admin.employees.edit', ['employeeId' => 'employee-1']))
            ->assertRedirect(route('login'));
    }

    public function test_kasir_is_redirected_back_to_cashier_dashboard_when_accessing_edit_employee_page(): void
    {
        $response = $this->actingAs($this->createUserWithRole('kasir-employee-edit@example.test', 'kasir'))
            ->get(route('admin.employees.edit', ['employeeId' => 'employee-1']));

        $response->assertRedirect(route('cashier.dashboard'));
        $response->assertSessionHas('error', 'Halaman admin hanya untuk role admin.');
    }

    public function test_admin_can_access_edit_employee_page(): void
    {
        $employeeId = $this->seedEmployee();

        $response = $this->actingAs($this->createUserWithRole('admin-employee-edit@example.test', 'admin'))
            ->get(route('admin.employees.edit', ['employeeId' => $employeeId]));

        $response->assertOk();
        $response->assertSee('Edit Data Karyawan');
        $response->assertSee('Catatan Perubahan');
        $response->assertSee('Nonaktif');
    }

    public function test_admin_is_redirected_to_index_when_employee_is_missing(): void
    {
        $response = $this->actingAs($this->createUserWithRole('admin-employee-missing@example.test', 'admin'))
            ->get(route('admin.employees.edit', ['employeeId' => 'missing-employee']));

        $response->assertRedirect(route('admin.employees.index'));
        $response->assertSessionHas('error', 'Data karyawan tidak ditemukan.');
    }

    public function test_admin_can_update_employee_and_write_audit_log(): void
    {
        $employeeId = $this->seedEmployee();

        $admin = $this->createUserWithRole('admin-employee-update@example.test', 'admin');

        $response = $this->actingAs($admin)
            ->put(route('admin.employees.update', ['employeeId' => $employeeId]), [
                'name' => 'Budi Santoso Update',
                'phone' => '081299999999',
                'base_salary_amount' => 5500000,
                'pay_period_value' => 'monthly',
                'status_value' => 'inactive',
                'change_reason' => 'Cuti panjang sementara.',
            ]);

        $response->assertRedirect(route('admin.employees.index'));
        $response->assertSessionHas('success', 'Data karyawan berhasil diperbarui.');

        $this->assertDatabaseHas('employees', [
            'id' => $employeeId,
            'name' => 'Budi Santoso Update',
            'phone' => '081299999999',
            'base_salary' => 5500000,
            'pay_period' => 'monthly',
            'status' => 'inactive',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'employee_profile_updated',
        ]);

        $audit = DB::table('audit_logs')
            ->where('event', 'employee_profile_updated')
            ->latest('id')
            ->first();

        $this->assertNotNull($audit);

        $context = json_decode((string) $audit->context, true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame($employeeId, $context['employee_id']);
        $this->assertSame((string) $admin->getAuthIdentifier(), $context['performed_by_actor_id']);
        $this->assertSame('Cuti panjang sementara.', $context['reason']);
        $this->assertSame('Budi Santoso', $context['before']['name']);
        $this->assertSame('weekly', $context['before']['pay_period_value']);
        $this->assertSame('active', $context['before']['status_value']);
        $this->assertSame('Budi Santoso Update', $context['after']['name']);
        $this->assertSame('monthly', $context['after']['pay_period_value']);
        $this->assertSame('inactive', $context['after']['status_value']);
    }

    public function test_admin_cannot_update_employee_when_change_reason_is_blank(): void
    {
        $employeeId = $this->seedEmployee();

        $response = $this->from(route('admin.employees.edit', ['employeeId' => $employeeId]))
            ->actingAs($this->createUserWithRole('admin-employee-update-blank@example.test', 'admin'))
            ->put(route('admin.employees.update', ['employeeId' => $employeeId]), [
                'name' => 'Budi Santoso Update',
                'phone' => '081299999999',
                'base_salary_amount' => 5500000,
                'pay_period_value' => 'monthly',
                'status_value' => 'inactive',
                'change_reason' => '   ',
            ]);

        $response->assertRedirect(route('admin.employees.edit', ['employeeId' => $employeeId]));
        $response->assertSessionHasErrors([
            'employee' => 'Catatan perubahan wajib diisi.',
        ]);

        $this->assertDatabaseHas('employees', [
            'id' => $employeeId,
            'name' => 'Budi Santoso',
            'phone' => '081211111111',
            'base_salary' => 5000000,
            'pay_period' => 'weekly',
            'status' => 'active',
        ]);

        $this->assertDatabaseMissing('audit_logs', [
            'event' => 'employee_profile_updated',
        ]);
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
