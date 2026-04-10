<?php

declare(strict_types=1);

namespace Tests\Feature\EmployeeFinance;

use App\Adapters\Out\Audit\DatabaseAuditLogAdapter;
use App\Adapters\Out\EmployeeFinance\DatabaseEmployeeReaderAdapter;
use App\Adapters\Out\EmployeeFinance\DatabasePayrollDisbursementWriterAdapter;
use App\Application\EmployeeFinance\UseCases\DisbursePayrollBatchHandler;
use App\Application\EmployeeFinance\UseCases\PayrollBatchRowProcessor;
use App\Application\Shared\DTO\Result;
use App\Ports\Out\TransactionManagerPort;
use App\Ports\Out\UuidPort;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class DisbursePayrollBatchFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_handler_stores_atomic_batch_and_writes_summary_and_row_audit_logs(): void
    {
        $this->seedEmployee('11111111-1111-1111-1111-111111111111', 'Budi', 'active');
        $this->seedEmployee('22222222-2222-2222-2222-222222222222', 'Andi', 'active');

        $result = $this->buildHandler([
            'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa',
            'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb',
            'cccccccc-cccc-cccc-cccc-cccccccccccc',
        ])->handle('owner-1', '2026-03-25', 'monthly', 'Batch Maret', [
            ['employee_id' => '11111111-1111-1111-1111-111111111111', 'amount' => 5000000],
            ['employee_id' => '22222222-2222-2222-2222-222222222222', 'amount' => 2500000, 'mode_value_override' => 'weekly', 'notes_override' => 'Override row'],
        ]);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertTrue($result->isSuccess());
        $this->assertDatabaseCount('payroll_disbursements', 2);
        $this->assertDatabaseHas('payroll_disbursements', [
            'id' => 'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb',
            'mode' => 'monthly',
            'notes' => 'Batch Maret',
        ]);
        $this->assertDatabaseHas('payroll_disbursements', [
            'id' => 'cccccccc-cccc-cccc-cccc-cccccccccccc',
            'mode' => 'weekly',
            'notes' => 'Override row',
        ]);
        $this->assertSame(2, DB::table('audit_logs')->where('event', 'payroll_disbursement_recorded')->count());
        $this->assertDatabaseHas('audit_logs', ['event' => 'payroll_batch_disbursement_recorded']);

        $context = json_decode(
            (string) DB::table('audit_logs')->where('event', 'payroll_batch_disbursement_recorded')->value('context'),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $this->assertSame('aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa', $context['batch_id']);
        $this->assertSame('owner-1', $context['performed_by_actor_id']);
        $this->assertSame(2, $context['row_count']);
        $this->assertSame(7500000, $context['total_amount']);
    }

    public function test_handler_rejects_batch_when_one_employee_is_inactive(): void
    {
        $this->seedEmployee('11111111-1111-1111-1111-111111111111', 'Budi', 'active');
        $this->seedEmployee('22222222-2222-2222-2222-222222222222', 'Andi', 'inactive');

        $result = $this->buildHandler([
            'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa',
            'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb',
        ])->handle('owner-1', '2026-03-25', 'monthly', null, [
            ['employee_id' => '11111111-1111-1111-1111-111111111111', 'amount' => 5000000],
            ['employee_id' => '22222222-2222-2222-2222-222222222222', 'amount' => 2500000],
        ]);

        $this->assertTrue($result->isFailure());
        $this->assertSame(['payroll_batch' => ['EMPLOYEE_INACTIVE']], $result->errors());
        $this->assertDatabaseCount('payroll_disbursements', 0);
        $this->assertDatabaseCount('audit_logs', 0);
    }

    private function buildHandler(array $ids): DisbursePayrollBatchHandler
    {
        $uuid = new class ($ids) implements UuidPort {
            public function __construct(private array $ids)
            {
            }

            public function generate(): string
            {
                $next = array_shift($this->ids);

                if (! is_string($next)) {
                    throw new \RuntimeException('UUID test fixture exhausted.');
                }

                return $next;
            }
        };

        return new DisbursePayrollBatchHandler(
            new PayrollBatchRowProcessor(
                new DatabaseEmployeeReaderAdapter(),
                new DatabasePayrollDisbursementWriterAdapter(),
                new DatabaseAuditLogAdapter(),
                $uuid,
            ),
            new DatabaseAuditLogAdapter(),
            $uuid,
            new class () implements TransactionManagerPort {
                public function begin(): void { DB::beginTransaction(); }
                public function commit(): void { DB::commit(); }
                public function rollBack(): void { DB::rollBack(); }
            },
        );
    }

    private function seedEmployee(string $id, string $name, string $status): void
    {
        DB::table('employees')->insert([
            'id' => $id,
            'employee_name' => $name,
            'phone' => '0812',
            'default_salary_amount' => 5000000,
            'salary_basis_type' => 'monthly',
            'employment_status' => $status,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
