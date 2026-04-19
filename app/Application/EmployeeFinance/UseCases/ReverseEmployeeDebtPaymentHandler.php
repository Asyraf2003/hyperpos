<?php

// @audit-skip: line-limit
declare(strict_types=1);

namespace App\Application\EmployeeFinance\UseCases;

use App\Core\Shared\Exceptions\DomainException;
use App\Ports\Out\AuditLogPort;
use App\Ports\Out\EmployeeFinance\EmployeeDebtPaymentReversalWriterPort;
use App\Ports\Out\TransactionManagerPort;
use App\Ports\Out\UuidPort;
use Illuminate\Support\Facades\DB;
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
            $payment = DB::table('employee_debt_payments')
                ->join('employee_debts', 'employee_debts.id', '=', 'employee_debt_payments.employee_debt_id')
                ->select([
                    'employee_debt_payments.id',
                    'employee_debt_payments.employee_debt_id',
                    'employee_debt_payments.amount',
                    'employee_debt_payments.payment_date',
                    'employee_debt_payments.notes',
                    'employee_debts.employee_id',
                    'employee_debts.total_debt',
                    'employee_debts.remaining_balance',
                    'employee_debts.status',
                ])
                ->where('employee_debt_payments.id', $paymentId)
                ->first();

            if ($payment === null) {
                throw new InvalidArgumentException('Data pembayaran hutang tidak ditemukan.');
            }

            $alreadyReversed = DB::table('employee_debt_payment_reversals')
                ->where('employee_debt_payment_id', $paymentId)
                ->exists();

            if ($alreadyReversed) {
                throw new DomainException('Pembayaran hutang ini sudah direversal.');
            }

            $beforeRemainingBalance = (int) $payment->remaining_balance;
            $paymentAmount = (int) $payment->amount;
            $afterRemainingBalance = $beforeRemainingBalance + $paymentAmount;
            $totalDebt = (int) $payment->total_debt;

            if ($afterRemainingBalance > $totalDebt) {
                throw new DomainException('Reversal pembayaran membuat sisa hutang melebihi total hutang.');
            }

            $beforeStatus = (string) $payment->status;
            $afterStatus = $afterRemainingBalance === 0 ? 'paid' : 'unpaid';

            DB::table('employee_debts')
                ->where('id', (string) $payment->employee_debt_id)
                ->update([
                    'remaining_balance' => $afterRemainingBalance,
                    'status' => $afterStatus,
                    'updated_at' => now(),
                ]);

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
                'employee_debt_id' => (string) $payment->employee_debt_id,
                'employee_id' => (string) $payment->employee_id,
                'amount' => $paymentAmount,
                'payment_date' => (string) $payment->payment_date,
                'notes' => $payment->notes !== null ? (string) $payment->notes : null,
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
