<?php

declare(strict_types=1);

namespace Database\Seeders\CreateOnly;

use Database\Seeders\CreateOnly\Support\CreateOnlySeeder;
use Database\Seeders\CreateOnly\Support\CreateOnlySeedCalendar;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class CreatePayrollDisbursementSeeder extends CreateOnlySeeder
{
    private const TARGET_TABLE = 'payroll_disbursements';

    /**
     * @var list<array{
     *   id:string,
     *   employee_index:int,
     *   amount:int,
     *   day:int,
     *   time:string,
     *   mode:string,
     *   notes:string
     * }>
     */
    private const PAYROLL_SCENARIOS = [
        [
            'id' => '00000000-0000-5300-0001-000000000001',
            'employee_index' => 13,
            'amount' => 125000,
            'day' => 20,
            'time' => '15:10:00',
            'mode' => 'daily',
            'notes' => 'Seed payroll harian aktif - shift pagi',
        ],
        [
            'id' => '00000000-0000-5300-0001-000000000002',
            'employee_index' => 14,
            'amount' => 875000,
            'day' => 20,
            'time' => '15:20:00',
            'mode' => 'weekly',
            'notes' => 'Seed payroll mingguan aktif - minggu berjalan',
        ],
        [
            'id' => '00000000-0000-5300-0001-000000000003',
            'employee_index' => 15,
            'amount' => 2650000,
            'day' => 20,
            'time' => '15:30:00',
            'mode' => 'monthly',
            'notes' => 'Seed payroll bulanan aktif - gaji pokok',
        ],
        [
            'id' => '00000000-0000-5300-0001-000000000004',
            'employee_index' => 16,
            'amount' => 150000,
            'day' => 21,
            'time' => '09:10:00',
            'mode' => 'daily',
            'notes' => 'Seed payroll harian aktif - shift tambahan',
        ],
        [
            'id' => '00000000-0000-5300-0001-000000000005',
            'employee_index' => 17,
            'amount' => 925000,
            'day' => 21,
            'time' => '09:20:00',
            'mode' => 'weekly',
            'notes' => 'Seed payroll mingguan aktif - bonus hadir',
        ],
        [
            'id' => '00000000-0000-5300-0001-000000000006',
            'employee_index' => 18,
            'amount' => 2800000,
            'day' => 21,
            'time' => '09:30:00',
            'mode' => 'monthly',
            'notes' => 'Seed payroll bulanan aktif - gaji teknisi',
        ],
    ];

    public function run(): void
    {
        $this->assertLocalOrTesting();

        $employeeIds = $this->activeEmployeeIds();

        $created = 0;

        foreach (self::PAYROLL_SCENARIOS as $scenario) {
            $this->assertScenarioValid($scenario);

            $employeeId = $employeeIds[$scenario['employee_index']] ?? null;

            if ($employeeId === null) {
                throw new RuntimeException('Not enough active employees to seed payroll disbursements.');
            }

            if ($this->payrollExists($scenario['id'])) {
                $this->assertExistingPayrollMatches($scenario['id'], $employeeId, $scenario);
                continue;
            }

            $disbursementDate = $this->scenarioDateTime($scenario);

            if ($this->createOnly(self::TARGET_TABLE, 'id', $scenario['id'], [
                'id' => $scenario['id'],
                'employee_id' => $employeeId,
                'amount' => $scenario['amount'],
                'disbursement_date' => $disbursementDate,
                'mode' => $scenario['mode'],
                'notes' => $scenario['notes'],
                'created_at' => $disbursementDate,
                'updated_at' => $disbursementDate,
            ])) {
                $created++;
            }
        }

        $this->command?->info(sprintf(
            'create-only payroll disbursements: planned=%d created=%d',
            count(self::PAYROLL_SCENARIOS),
            $created
        ));
    }


    /**
     * @return list<string>
     */
    private function activeEmployeeIds(): array
    {
        return DB::table('employees')
            ->where('employment_status', 'active')
            ->orderBy('id')
            ->limit(30)
            ->pluck('id')
            ->map(static fn (mixed $id): string => (string) $id)
            ->values()
            ->all();
    }

    /**
     * @param array<string, mixed> $scenario
     */
    private function assertScenarioValid(array $scenario): void
    {
        if ((int) $scenario['amount'] <= 0) {
            throw new RuntimeException(sprintf('Payroll amount must be positive: %s.', $scenario['id']));
        }

        if (! in_array((string) $scenario['mode'], ['daily', 'weekly', 'monthly'], true)) {
            throw new RuntimeException(sprintf('Invalid payroll mode: %s.', $scenario['id']));
        }
    }

    /**
     * @param array<string, mixed> $scenario
     */
    private function scenarioDateTime(array $scenario): string
    {
        return CreateOnlySeedCalendar::currentMonthDate((int) $scenario['day'])
            .' '
            .(string) $scenario['time'];
    }


    private function payrollExists(string $id): bool
    {
        return DB::table(self::TARGET_TABLE)
            ->where('id', $id)
            ->exists();
    }

    /**
     * @param array<string, mixed> $scenario
     */
    private function assertExistingPayrollMatches(string $id, string $employeeId, array $scenario): void
    {
        $row = DB::table(self::TARGET_TABLE)
            ->where('id', $id)
            ->first();

        if ($row === null) {
            throw new RuntimeException(sprintf('Expected existing payroll missing: %s.', $id));
        }

        $expected = [
            'employee_id' => $employeeId,
            'amount' => (int) $scenario['amount'],
            'disbursement_date' => $this->scenarioDateTime($scenario),
            'mode' => (string) $scenario['mode'],
        ];

        $actual = [
            'employee_id' => (string) $row->employee_id,
            'amount' => (int) $row->amount,
            'disbursement_date' => (string) $row->disbursement_date,
            'mode' => (string) $row->mode,
        ];

        if ($actual !== $expected) {
            throw new RuntimeException(sprintf('Existing payroll differs from seed contract: %s.', $id));
        }
    }
}
