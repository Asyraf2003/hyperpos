<?php

declare(strict_types=1);

namespace Tests\Feature\EmployeeFinance;

use App\Application\EmployeeFinance\UseCases\PayEmployeeDebtHandler;
use App\Core\Shared\Exceptions\DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

final class PayEmployeeDebtFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_pay_employee_debt_handler_records_payment_reduces_balance_and_writes_audit_log(): void
    {
        $employeeId = (string) Str::uuid();
        $debtId = (string) Str::uuid();

        DB::table('employees')->insert([
            'id' => $employeeId,
            'employee_name' => 'Asyraf Hutang',
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
        ]);

        $handler = app(PayEmployeeDebtHandler::class);

        $paymentId = $handler->handle($debtId, 250000, 'Cicilan pertama');

        $this->assertIsString($paymentId);

        $this->assertDatabaseHas('employee_debt_payments', [
            'id' => $paymentId,
            'employee_debt_id' => $debtId,
            'amount' => 250000,
            'notes' => 'Cicilan pertama',
        ]);

        $this->assertDatabaseHas('employee_debts', [
            'id' => $debtId,
            'remaining_balance' => 750000,
            'status' => 'unpaid',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'employee_debt_payment_recorded',
        ]);

        $audit = DB::table('audit_logs')
            ->where('event', 'employee_debt_payment_recorded')
            ->latest('id')
            ->first();

        $this->assertNotNull($audit);

        $context = json_decode((string) $audit->context, true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame($debtId, $context['employee_debt_id']);
        $this->assertSame($employeeId, $context['employee_id']);
        $this->assertSame($paymentId, $context['payment_id']);
        $this->assertSame(250000, $context['amount']);
        $this->assertSame('Cicilan pertama', $context['notes']);
        $this->assertNull($context['performed_by_actor_id']);
        $this->assertSame(1000000, $context['before']['remaining_balance']);
        $this->assertSame('unpaid', $context['before']['status']);
        $this->assertSame(750000, $context['after']['remaining_balance']);
        $this->assertSame('unpaid', $context['after']['status']);
    }

    public function test_pay_employee_debt_handler_rejects_payment_exceeding_remaining_balance(): void
    {
        $employeeId = (string) Str::uuid();
        $debtId = (string) Str::uuid();

        DB::table('employees')->insert([
            'id' => $employeeId,
            'employee_name' => 'Asyraf Overpay',
            'phone' => '081122223333',
            'default_salary_amount' => 5000000,
            'salary_basis_type' => 'monthly',
            'employment_status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('employee_debts')->insert([
            'id' => $debtId,
            'employee_id' => $employeeId,
            'total_debt' => 500000,
            'remaining_balance' => 200000,
            'status' => 'unpaid',
            'notes' => 'Kasbon operasional',
            'created_at' => now(),
        ]);

        $handler = app(PayEmployeeDebtHandler::class);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Nominal pembayaran melebihi sisa hutang.');

        $handler->handle($debtId, 300000, 'Overpay');

        $this->assertDatabaseMissing('audit_logs', [
            'event' => 'employee_debt_payment_recorded',
        ]);
    }
}
