<?php

declare(strict_types=1);

namespace Tests\Feature\EmployeeFinance;

use App\Application\EmployeeFinance\UseCases\RegisterEmployeeHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class RegisterEmployeeFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_employee_handler_creates_record_in_database(): void
    {
        $handler = app(RegisterEmployeeHandler::class);

        $id = $handler->handle(
            'Asyraf Mubarak',
            '08111222333',
            5000000,
            'monthly'
        );

        $this->assertIsString($id);

        $this->assertDatabaseHas('employees', [
            'id' => $id,
            'name' => 'Asyraf Mubarak',
            'phone' => '08111222333',
            'base_salary' => 5000000,
            'pay_period' => 'monthly',
            'status' => 'active',
        ]);
    }
}
