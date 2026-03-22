<?php

declare(strict_types=1);

namespace App\Application\EmployeeFinance\UseCases;

use App\Core\EmployeeFinance\Employee\EmployeeStatus;
use App\Core\EmployeeFinance\Payroll\DisbursementMode;
use App\Core\EmployeeFinance\Payroll\PayrollDisbursement;
use App\Core\Shared\ValueObjects\Money;
use App\Ports\Out\AuditLogPort;
use App\Ports\Out\EmployeeFinance\EmployeeReaderPort;
use App\Ports\Out\EmployeeFinance\PayrollDisbursementWriterPort;
use App\Ports\Out\UuidPort;
use DateTimeImmutable;

final class PayrollBatchRowProcessor
{
    public function __construct(
        private EmployeeReaderPort $employeeReader,
        private PayrollDisbursementWriterPort $payrollWriter,
        private AuditLogPort $auditLog,
        private UuidPort $uuidPort,
    ) {
    }

    public function process(
        string $batchId,
        string $performedByActorId,
        DateTimeImmutable $date,
        DisbursementMode $defaultMode,
        ?string $defaultNotes,
        array $row,
        int $index,
    ): array {
        $employee = $this->employeeReader->findById((string) $row['employee_id']);

        if ($employee === null) {
            return ['error' => true, 'result' => \App\Application\Shared\DTO\Result::failure('Baris '.($index + 1).': karyawan tidak ditemukan.', ['payroll_batch' => ['EMPLOYEE_NOT_FOUND']])];
        }

        if ($employee->getStatus() !== EmployeeStatus::ACTIVE) {
            return ['error' => true, 'result' => \App\Application\Shared\DTO\Result::failure('Baris '.($index + 1).': karyawan nonaktif tidak boleh dicairkan.', ['payroll_batch' => ['EMPLOYEE_INACTIVE']])];
        }

        $payrollId = $this->uuidPort->generate();
        $amount = (int) $row['amount'];
        $mode = $this->resolveMode($row, $defaultMode);
        $notes = $this->nullableString($row['notes_override'] ?? null) ?? $defaultNotes;

        $payroll = PayrollDisbursement::disburse(
            $payrollId,
            $employee->getId(),
            Money::fromInt($amount),
            $date,
            $mode,
            $notes,
        );

        $this->payrollWriter->save($payroll);
        $this->auditLog->record('payroll_disbursement_recorded', [
            'batch_id' => $batchId,
            'payroll_id' => $payrollId,
            'employee_id' => $employee->getId(),
            'amount' => $amount,
            'disbursement_date' => $date->format('Y-m-d'),
            'mode' => $mode->value,
            'notes' => $notes,
            'performed_by_actor_id' => $performedByActorId,
        ]);

        return ['error' => false, 'payroll_id' => $payrollId, 'employee_id' => $employee->getId(), 'amount' => $amount];
    }

    private function resolveMode(array $row, DisbursementMode $defaultMode): DisbursementMode
    {
        $value = isset($row['mode_value_override']) && is_string($row['mode_value_override']) && trim($row['mode_value_override']) !== ''
            ? (string) $row['mode_value_override']
            : $defaultMode->value;

        return DisbursementMode::from($value);
    }

    private function nullableString(mixed $value): ?string
    {
        if (! is_string($value)) return null;
        $trimmed = trim($value);
        return $trimmed === '' ? null : $trimmed;
    }
}
