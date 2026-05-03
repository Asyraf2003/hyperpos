<?php

// @audit-skip: line-limit
declare(strict_types=1);

namespace App\Application\EmployeeFinance\UseCases;

use App\Core\Shared\Exceptions\DomainException;
use App\Ports\Out\AuditLogPort;
use App\Ports\Out\EmployeeFinance\EmployeeDebtPaymentReversalWriterPort;
use App\Ports\Out\TransactionManagerPort;
use App\Ports\Out\UuidPort;
use InvalidArgumentException;
use Throwable;

final class ReverseEmployeeDebtPaymentHandler
{
    public function __construct(
        private EmployeeDebtPaymentReversalWriterPort $reversalWriter,
        private AuditLogPort $auditLog,
        private UuidPort $uuidPort,
        private TransactionManagerPort $transactionManager,
    ) {
    }

    public function handle(string $paymentId, string $reason, string $actorId): string
    {
        if (trim($reason) === '') {
            throw new DomainException('Catatan reversal wajib diisi.');
        }

        $this->transactionManager->begin();

        try {
            $payment = $this->reversalWriter->findPaymentSnapshotForReversal($paymentId);

            if ($payment === null) {
                throw new InvalidArgumentException('Data pembayaran hutang tidak ditemukan.');
            }

            if ($this->reversalWriter->paymentAlreadyReversed($paymentId)) {
                throw new DomainException('Pembayaran hutang ini sudah direversal.');
            }

            $beforeRemainingBalance = $payment['remaining_balance'];
            $paymentAmount = $payment['amount'];
            $afterRemainingBalance = $beforeRemainingBalance + $paymentAmount;
            $totalDebt = $payment['total_debt'];

            if ($afterRemainingBalance > $totalDebt) {
                throw new DomainException('Reversal pembayaran membuat sisa hutang melebihi total hutang.');
            }

            $beforeStatus = $payment['status'];
            $afterStatus = $afterRemainingBalance === 0 ? 'paid' : 'unpaid';

            $this->reversalWriter->updateDebtAfterPaymentReversal(
                $payment['employee_debt_id'],
                $afterRemainingBalance,
                $afterStatus
            );

            $reversalId = $this->uuidPort->generate();

            $this->reversalWriter->record([
                'id' => $reversalId,
                'employee_debt_payment_id' => $paymentId,
                'reason' => $reason,
                'performed_by_actor_id' => $actorId,
            ]);

            $this->auditLog->record('employee_debt_payment_reversed', [
                'reversal_id' => $reversalId,
                'payment_id' => $paymentId,
                'employee_debt_id' => $payment['employee_debt_id'],
                'employee_id' => $payment['employee_id'],
                'amount' => $paymentAmount,
                'payment_date' => $payment['payment_date'],
                'notes' => $payment['notes'],
                'reason' => $reason,
                'performed_by_actor_id' => $actorId,
                'before' => [
                    'remaining_balance' => $beforeRemainingBalance,
                    'status' => $beforeStatus,
                ],
                'after' => [
                    'remaining_balance' => $afterRemainingBalance,
                    'status' => $afterStatus,
                ],
            ]);

            $this->transactionManager->commit();

            return $reversalId;
        } catch (Throwable $e) {
            $this->transactionManager->rollBack();
            throw $e;
        }
    }
}
