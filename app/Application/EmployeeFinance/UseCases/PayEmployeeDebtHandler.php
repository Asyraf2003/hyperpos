<?php

declare(strict_types=1);

namespace App\Application\EmployeeFinance\UseCases;

use App\Core\EmployeeFinance\EmployeeDebt\DebtPayment;
use App\Core\Shared\ValueObjects\Money;
use App\Ports\Out\AuditLogPort;
use App\Ports\Out\ClockPort;
use App\Ports\Out\EmployeeFinance\EmployeeDebtReaderPort;
use App\Ports\Out\EmployeeFinance\EmployeeDebtWriterPort;
use App\Ports\Out\TransactionManagerPort;
use App\Ports\Out\UuidPort;
use InvalidArgumentException;
use Throwable;

final class PayEmployeeDebtHandler
{
    public function __construct(
        private EmployeeDebtReaderPort $debtReader,
        private EmployeeDebtWriterPort $debtWriter,
        private ClockPort $clockPort,
        private UuidPort $uuidPort,
        private TransactionManagerPort $transactionManager,
        private AuditLogPort $auditLog,
    ) {
    }

    public function handle(
        string $debtId,
        int $paymentAmount,
        ?string $notes = null,
        ?string $performedByActorId = null,
    ): string {
        $this->transactionManager->begin();

        try {
            $debt = $this->debtReader->findById($debtId);

            if ($debt === null) {
                throw new InvalidArgumentException('Data hutang karyawan tidak ditemukan.');
            }

            $beforeRemainingBalance = $debt->getRemainingBalance()->amount();
            $beforeStatus = $debt->getStatus()->value;

            $paymentId = $this->uuidPort->generate();
            $amount = Money::fromInt($paymentAmount);
            $paymentDate = $this->clockPort->now();

            $payment = new DebtPayment($paymentId, $amount, $paymentDate, $notes);

            $debt->pay($payment);

            $this->debtWriter->save($debt);

            $this->auditLog->record('employee_debt_payment_recorded', [
                'employee_debt_id' => $debtId,
                'employee_id' => $debt->getEmployeeId(),
                'payment_id' => $paymentId,
                'amount' => $paymentAmount,
                'payment_date' => $paymentDate->format('Y-m-d H:i:s'),
                'notes' => $notes,
                'performed_by_actor_id' => $performedByActorId,
                'before' => [
                    'remaining_balance' => $beforeRemainingBalance,
                    'status' => $beforeStatus,
                ],
                'after' => [
                    'remaining_balance' => $debt->getRemainingBalance()->amount(),
                    'status' => $debt->getStatus()->value,
                ],
            ]);

            $this->transactionManager->commit();

            return $paymentId;
        } catch (Throwable $e) {
            $this->transactionManager->rollBack();
            throw $e;
        }
    }
}
