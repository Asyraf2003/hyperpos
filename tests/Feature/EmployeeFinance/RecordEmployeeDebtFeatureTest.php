<?php

declare(strict_types=1);

namespace Tests\Feature\EmployeeFinance;

use App\Application\EmployeeFinance\UseCases\RecordEmployeeDebtHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use InvalidArgumentException;

final class RecordEmployeeDebtFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_record_employee_debt_handler_creates_debt_record(): void
    {
        // Setup data persis seperti gaya Anda
        DB::table('employees')->insert([
            'id' => 'emp-1',
            'name' => 'Montir A',
            'base_salary' => 2000000,
            'pay_period' => 'weekly',
            'status' => 'active',
        ]);

        $handler = app(RecordEmployeeDebtHandler::class);

        $debtId = $handler->handle(
            'emp-1',
            1000000,
            'Pinjaman darurat'
        );

        $this->assertIsString($debtId);

        $this->assertDatabaseHas('employee_debts', [
            'id' => $debtId,
            'employee_id' => 'emp-1',
            'total_debt' => 1000000,
            'remaining_balance' => 1000000,
            'status' => 'unpaid',
            'notes' => 'Pinjaman darurat',
        ]);
    }

    public function test_record_employee_debt_handler_rejects_when_employee_not_found(): void
    {
        $handler = app(RecordEmployeeDebtHandler::class);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Karyawan tidak ditemukan.');

        $handler->handle(
            'invalid-emp-id',
            1000000,
            null
        );

        $this->assertDatabaseCount('employee_debts', 0);
    }
}
