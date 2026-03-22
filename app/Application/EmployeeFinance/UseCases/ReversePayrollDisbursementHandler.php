<?php

declare(strict_types=1);

namespace App\Application\EmployeeFinance\UseCases;

use App\Core\Shared\Exceptions\DomainException;
use App\Ports\Out\AuditLogPort;
use App\Ports\Out\EmployeeFinance\PayrollDisbursementReversalWriterPort;
use App\Ports\Out\TransactionManagerPort;
use App\Ports\Out\UuidPort;
use Illuminate\Support\Facades\DB;
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
            $payroll = DB::table('payroll_disbursements')
                ->select(['id', 'employee_id', 'amount', 'disbursement_date', 'mode', 'notes'])
                ->where('id', $payrollId)
                ->first();

            if ($payroll === null) {
                throw new InvalidArgumentException('Data pencairan gaji tidak ditemukan.');
            }

            $alreadyReversed = DB::table('payroll_disbursement_reversals')
                ->where('payroll_disbursement_id', $payrollId)
                ->exists();

            if ($alreadyReversed) {
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
                'employee_id' => (string) $payroll->employee_id,
                'amount' => (int) $payroll->amount,
                'disbursement_date' => (string) $payroll->disbursement_date,
                'mode' => (string) $payroll->mode,
                'notes' => $payroll->notes !== null ? (string) $payroll->notes : null,
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
