<?php

declare(strict_types=1);

namespace App\Application\EmployeeFinance\UseCases;

use App\Core\EmployeeFinance\Employee\Employee;
use App\Core\EmployeeFinance\Employee\EmployeeStatus;
use App\Core\EmployeeFinance\Employee\PayPeriod;
use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;
use App\Ports\Out\AuditLogPort;
use App\Ports\Out\EmployeeFinance\EmployeeReaderPort;
use App\Ports\Out\EmployeeFinance\EmployeeWriterPort;
use App\Ports\Out\TransactionManagerPort;
use InvalidArgumentException;
use Throwable;

final class UpdateEmployeeProfileHandler
{
    public function __construct(
        private EmployeeReaderPort $employeeReader,
        private EmployeeWriterPort $employeeWriter,
        private AuditLogPort $auditLog,
        private TransactionManagerPort $transactionManager,
    ) {
    }

    public function handle(
        string $employeeId,
        string $name,
        ?string $phone,
        int $baseSalaryAmount,
        string $payPeriodValue,
        string $statusValue,
        string $changeReason,
        string $performedByActorId,
    ): void {
        if (trim($changeReason) === '') {
            throw new DomainException('Catatan perubahan wajib diisi.');
        }

        $this->transactionManager->begin();

        try {
            $employee = $this->employeeReader->findById($employeeId);

            if ($employee === null) {
                throw new InvalidArgumentException('Karyawan tidak ditemukan.');
            }

            $before = $this->snapshot($employee);

            $employee->updateProfile($name, $phone, PayPeriod::from($payPeriodValue));
            $employee->updateBaseSalary(Money::fromInt($baseSalaryAmount), $changeReason);

            if ($statusValue === EmployeeStatus::INACTIVE->value) {
                $employee->deactivate();
            } else {
                $employee->activate();
            }

            $this->employeeWriter->save($employee);

            $this->auditLog->record('employee_profile_updated', [
                'employee_id' => $employeeId,
                'performed_by_actor_id' => $performedByActorId,
                'reason' => $changeReason,
                'before' => $before,
                'after' => $this->snapshot($employee),
            ]);

            $this->transactionManager->commit();
        } catch (Throwable $e) {
            $this->transactionManager->rollBack();
            throw $e;
        }
    }

    private function snapshot(Employee $employee): array
    {
        return [
            'name' => $employee->getName(),
            'phone' => $employee->getPhone(),
            'base_salary_amount' => $employee->getBaseSalary()->amount(),
            'pay_period_value' => $employee->getPayPeriod()->value,
            'status_value' => $employee->getStatus()->value,
        ];
    }
}
