<?php

declare(strict_types=1);

namespace Tests\Feature\EmployeeFinance;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class EmployeeTableDataQueryFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_search_employee_table_with_multi_word_query(): void
    {
        $this->seedEmployeeRow('emp-1', 'Budi Santoso', '08121111', 5000000, 'weekly', 'active');
        $this->seedEmployeeRow('emp-2', 'Andi', '08999999', 4000000, 'monthly', 'inactive');
        $r = $this->actingAs($this->admin())->get(route('admin.employees.table', ['q' => 'Budi 0812']));
        $r->assertOk();
        $r->assertJsonCount(1, 'data.rows');
        $r->assertJsonPath('data.rows.0.name', 'Budi Santoso');
        $r->assertJsonPath('data.meta.filters.q', 'Budi 0812');
    }

    public function test_admin_can_sort_employee_table_by_base_salary_desc(): void
    {
        $this->seedEmployeeRow('emp-1', 'Budi', '0812', 3000000, 'weekly', 'active');
        $this->seedEmployeeRow('emp-2', 'Andi', '0822', 6000000, 'monthly', 'active');
        $r = $this->actingAs($this->admin())->get(route('admin.employees.table', ['sort_by' => 'base_salary', 'sort_dir' => 'desc']));
        $r->assertOk();
        $r->assertJsonPath('data.rows.0.name', 'Andi');
        $r->assertJsonPath('data.rows.1.name', 'Budi');
    }

    public function test_admin_can_access_second_page_of_employee_table(): void
    {
        for ($i = 1; $i <= 11; $i++) $this->seedEmployeeRow('emp-'.$i, 'Employee '.str_pad((string) $i, 2, '0', STR_PAD_LEFT), '08'.$i, 1000000 + $i, 'monthly', 'active');
        $r = $this->actingAs($this->admin())->get(route('admin.employees.table', ['page' => 2]));
        $r->assertOk();
        $r->assertJsonPath('data.meta.page', 2);
        $r->assertJsonPath('data.meta.last_page', 2);
        $r->assertJsonPath('data.rows.0.name', 'Employee 11');
    }

    private function admin(): User
    {
        $user = User::query()->create(['name' => 'Admin', 'email' => 'admin@example.test', 'password' => 'password123']);
        DB::table('actor_accesses')->insert(['actor_id' => (string) $user->getAuthIdentifier(), 'role' => 'admin']);
        return $user;
    }

    private function seedEmployeeRow(string $id, string $name, string $phone, int $salary, string $period, string $status): void
    {
        DB::table('employees')->insert(['id' => $id, 'name' => $name, 'phone' => $phone, 'base_salary' => $salary, 'pay_period' => $period, 'status' => $status]);
    }
}
