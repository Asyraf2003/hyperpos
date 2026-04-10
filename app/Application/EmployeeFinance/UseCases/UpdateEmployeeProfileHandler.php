<?php

declare(strict_types=1);

namespace App\Application\EmployeeFinance\UseCases;

use App\Application\EmployeeFinance\Context\EmployeeChangeContext;
use App\Application\EmployeeFinance\Support\EmployeeProfileAuditSnapshotBuilder;
use App\Application\EmployeeFinance\Support\EmployeeProfileValueCaster;
use App\Core\EmployeeFinance\Employee\EmployeeStatus;
use App\Core\EmployeeFinance\Employee\PayPeriod;
use App\Core\Shared\Exceptions\DomainException;
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
        private EmployeeChangeContext $changeContext,
        private EmployeeProfileAuditSnapshotBuilder $snapshotBuilder,
        private EmployeeProfileValueCaster $valueCaster,
    ) {
    }

    public function handle(
        string $employeeId,
        string $employeeName,
        ?string $phone,
        ?int $defaultSalaryAmount,
        string $salaryBasisType,
        string $employmentStatus,
        string $changeReason,
        string $performedByActorId,
        ?string $startedAt = null,
        ?string $endedAt = null,
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

            $before = $this->snapshotBuilder->build($employee);
            $this->changeContext->set($performedByActorId, 'admin', 'admin_web', $changeReason);

            $employee->updateProfile(
                $employeeName,
                $phone,
                PayPeriod::from($salaryBasisType),
                $this->valueCaster->parseOptionalDate($startedAt),
                $this->valueCaster->parseOptionalDate($endedAt),
            );

            $employee->updateDefaultSalaryAmount(
                $this->valueCaster->toNullableMoney($defaultSalaryAmount),
                $changeReason,
            );

            if ($employmentStatus === EmployeeStatus::INACTIVE->value) {
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
                'after' => $this->snapshotBuilder->build($employee),
            ]);

            $this->transactionManager->commit();
        } catch (Throwable $e) {
            $this->changeContext->clear();
            $this->transactionManager->rollBack();
            throw $e;
        }
    }
}
