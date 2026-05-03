<?php

declare(strict_types=1);

namespace App\Application\EmployeeFinance\UseCases;

use App\Core\Shared\Exceptions\DomainException;
use App\Ports\Out\AuditLogPort;
use App\Ports\Out\EmployeeFinance\PayrollDisbursementReversalWriterPort;
use App\Ports\Out\TransactionManagerPort;
use App\Ports\Out\UuidPort;
use InvalidArgumentException;
use Throwable;

final class ReversePayrollDisbursementHandler
{
    public function __construct(
        private PayrollDisbursementReversalWriterPort $reversalWriter,
        private AuditLogPort $auditLog,
        private UuidPort $uuidPort,
        private TransactionManagerPort $transactionManager,
    ) {
    }

    public function handle(string $payrollId, string $reason, string $actorId): string
    {
        if (trim($reason) === '') {
            throw new DomainException('Catatan reversal wajib diisi.');
        }

        $this->transactionManager->begin();

        try {
            $payroll = $this->reversalWriter->findPayrollSnapshotForReversal($payrollId);

            if ($payroll === null) {
                throw new InvalidArgumentException('Data pencairan gaji tidak ditemukan.');
            }

            if ($this->reversalWriter->payrollAlreadyReversed($payrollId)) {
                throw new DomainException('Pencairan gaji ini sudah direversal.');
            }

            $reversalId = $this->uuidPort->generate();

            $this->reversalWriter->record([
                'id' => $reversalId,
                'payroll_disbursement_id' => $payrollId,
                'reason' => $reason,
                'performed_by_actor_id' => $actorId,
            ]);

            $this->auditLog->record('payroll_disbursement_reversed', [
                'reversal_id' => $reversalId,
                'payroll_id' => $payrollId,
                'employee_id' => $payroll['employee_id'],
                'amount' => $payroll['amount'],
                'disbursement_date' => $payroll['disbursement_date'],
                'mode' => $payroll['mode'],
                'notes' => $payroll['notes'],
                'reason' => $reason,
                'performed_by_actor_id' => $actorId,
            ]);

            $this->transactionManager->commit();

            return $reversalId;
        } catch (Throwable $e) {
            $this->transactionManager->rollBack();
            throw $e;
        }
    }
}
