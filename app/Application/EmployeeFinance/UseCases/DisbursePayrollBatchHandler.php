<?php

declare(strict_types=1);

namespace App\Application\EmployeeFinance\UseCases;

use App\Application\Shared\DTO\Result;
use App\Core\EmployeeFinance\Employee\EmployeeStatus;
use App\Core\EmployeeFinance\Payroll\DisbursementMode;
use App\Core\EmployeeFinance\Payroll\PayrollDisbursement;
use App\Core\Shared\ValueObjects\Money;
use App\Ports\Out\AuditLogPort;
use App\Ports\Out\EmployeeFinance\EmployeeReaderPort;
use App\Ports\Out\EmployeeFinance\PayrollDisbursementWriterPort;
use App\Ports\Out\TransactionManagerPort;
use App\Ports\Out\UuidPort;
use DateTimeImmutable;
use Throwable;

final class DisbursePayrollBatchHandler
{
    public function __construct(
        private EmployeeReaderPort $employeeReader,
        private PayrollDisbursementWriterPort $payrollWriter,
        private AuditLogPort $auditLog,
        private UuidPort $uuidPort,
        private TransactionManagerPort $transactionManager,
    ) {
    }

    public function handle(
        string $performedByActorId,
        string $disbursementDateString,
        string $defaultModeValue,
        ?string $defaultNotes,
        array $rows,
    ): Result {
        if ($rows === []) {
            return Result::failure('Batch payroll minimal berisi satu baris.', ['payroll_batch' => ['BATCH_ROWS_REQUIRED']]);
        }

        $this->transactionManager->begin();

        try {
            $batchId = $this->uuidPort->generate();
            $date = new DateTimeImmutable($disbursementDateString);
            $defaultMode = DisbursementMode::from($defaultModeValue);
            $payrollIds = [];
            $employeeIds = [];
            $totalAmount = 0;

            foreach ($rows as $index => $row) {
                $employee = $this->employeeReader->findById((string) $row['employee_id']);

                if ($employee === null) {
                    $this->transactionManager->rollBack();
                    return Result::failure('Baris '.($index + 1).': karyawan tidak ditemukan.', ['payroll_batch' => ['EMPLOYEE_NOT_FOUND']]);
                }

                if ($employee->getStatus() !== EmployeeStatus::ACTIVE) {
                    $this->transactionManager->rollBack();
                    return Result::failure('Baris '.($index + 1).': karyawan nonaktif tidak boleh dicairkan.', ['payroll_batch' => ['EMPLOYEE_INACTIVE']]);
                }

                $payrollId = $this->uuidPort->generate();
                $amount = (int) $row['amount'];
                $mode = isset($row['mode_value_override']) && is_string($row['mode_value_override']) && trim($row['mode_value_override']) !== ''
                    ? DisbursementMode::from((string) $row['mode_value_override'])
                    : $defaultMode;
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

                $payrollIds[] = $payrollId;
                $employeeIds[] = $employee->getId();
                $totalAmount += $amount;
            }

            $this->auditLog->record('payroll_batch_disbursement_recorded', [
                'batch_id' => $batchId,
                'performed_by_actor_id' => $performedByActorId,
                'disbursement_date' => $date->format('Y-m-d'),
                'default_mode' => $defaultMode->value,
                'row_count' => count($rows),
                'total_amount' => $totalAmount,
                'employee_ids' => $employeeIds,
                'payroll_ids' => $payrollIds,
            ]);

            $this->transactionManager->commit();

            return Result::success([
                'batch_id' => $batchId,
                'row_count' => count($rows),
                'payroll_ids' => $payrollIds,
                'total_amount' => $totalAmount,
            ], 'Batch payroll berhasil dicatat.');
        } catch (Throwable $e) {
            $this->transactionManager->rollBack();
            throw $e;
        }
    }

    private function nullableString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }
}
