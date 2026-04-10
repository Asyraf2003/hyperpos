<?php

declare(strict_types=1);

namespace Tests\Feature\EmployeeFinance;

use App\Application\EmployeeFinance\UseCases\UpdateEmployeeBaseSalaryHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

final class UpdateEmployeeBaseSalaryFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_update_employee_base_salary_handler_updates_salary(): void
    {
        $employeeId = (string) Str::uuid();

        DB::table('employees')->insert([
            'id' => $employeeId,
            'employee_name' => 'Asyraf Gaji',
            'phone' => '081111111111',
            'default_salary_amount' => 5000000,
            'salary_basis_type' => 'monthly',
            'employment_status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $handler = app(UpdateEmployeeBaseSalaryHandler::class);

        $handler->handle($employeeId, 5500000);

        $this->assertDatabaseHas('employees', [
            'id' => $employeeId,
            'base_salary' => 5500000,
        ]);
    }

    public function test_update_employee_base_salary_handler_requires_reason_for_salary_reduction(): void
    {
        $employeeId = (string) Str::uuid();

        DB::table('employees')->insert([
            'id' => $employeeId,
            'employee_name' => 'Asyraf Turun Gaji',
            'phone' => '081122223333',
            'default_salary_amount' => 5000000,
            'salary_basis_type' => 'monthly',
            'employment_status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $handler = app(UpdateEmployeeBaseSalaryHandler::class);

        $this->expectException(\App\Core\Shared\Exceptions\DomainException::class);
        $this->expectExceptionMessage('Penurunan gaji pokok wajib menyertakan alasan.');

        $handler->handle($employeeId, 4500000);
    }
}
