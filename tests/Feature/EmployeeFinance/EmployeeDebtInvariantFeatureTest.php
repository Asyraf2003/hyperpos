<?php

declare(strict_types=1);

namespace Tests\Feature\EmployeeFinance;

use App\Application\EmployeeFinance\UseCases\AdjustEmployeeDebtPrincipalHandler;
use App\Application\EmployeeFinance\UseCases\PayEmployeeDebtHandler;
use App\Application\EmployeeFinance\UseCases\ReverseEmployeeDebtPaymentHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

final class EmployeeDebtInvariantFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_total_minus_remaining_matches_net_active_payments_after_payment_adjustment_and_reversal(): void
    {
        $employeeId = (string) Str::uuid();
        $debtId = (string) Str::uuid();
        $actorId = 'actor-invariant-test';

        DB::table('employees')->insert([
            'id' => $employeeId,
            'employee_name' => 'Invariant Hutang',
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

        $payHandler = app(PayEmployeeDebtHandler::class);
        $adjustHandler = app(AdjustEmployeeDebtPrincipalHandler::class);
        $reverseHandler = app(ReverseEmployeeDebtPaymentHandler::class);

        $firstPaymentId = $payHandler->handle($debtId, 100000, 'Pembayaran pertama', $actorId);

        $adjustHandler->handle(
            $debtId,
            'increase',
            50000,
            'Tambah principal untuk koreksi pencatatan',
            $actorId,
        );

        $adjustHandler->handle(
            $debtId,
            'decrease',
            25000,
            'Kurangi principal untuk koreksi lanjutan',
            $actorId,
        );

        $secondPaymentId = $payHandler->handle($debtId, 200000, 'Pembayaran kedua', $actorId);

        $reverseHandler->handle(
            $firstPaymentId,
            'Batalkan pembayaran pertama',
            $actorId,
        );

        $debt = DB::table('employee_debts')
            ->where('id', $debtId)
            ->first([
                'total_debt',
                'remaining_balance',
                'status',
            ]);

        $this->assertNotNull($debt);
        $this->assertSame(1025000, (int) $debt->total_debt);
        $this->assertSame(825000, (int) $debt->remaining_balance);
        $this->assertSame('unpaid', (string) $debt->status);

        $activePaymentTotal = (int) DB::table('employee_debt_payments')
            ->leftJoin(
                'employee_debt_payment_reversals',
                'employee_debt_payment_reversals.employee_debt_payment_id',
                '=',
                'employee_debt_payments.id'
            )
            ->where('employee_debt_payments.employee_debt_id', $debtId)
            ->whereNull('employee_debt_payment_reversals.id')
            ->sum('employee_debt_payments.amount');

        $this->assertSame(200000, $activePaymentTotal);
        $this->assertSame(
            $activePaymentTotal,
            (int) $debt->total_debt - (int) $debt->remaining_balance,
        );

        $this->assertDatabaseHas('employee_debt_payment_reversals', [
            'employee_debt_payment_id' => $firstPaymentId,
            'reason' => 'Batalkan pembayaran pertama',
        ]);

        $this->assertDatabaseMissing('employee_debt_payment_reversals', [
            'employee_debt_payment_id' => $secondPaymentId,
        ]);
    }
}
