<?php

declare(strict_types=1);

namespace App\Application\EmployeeFinance\UseCases;

use App\Core\EmployeeFinance\EmployeeDebt\DebtPayment;
use App\Core\Shared\ValueObjects\Money;
use App\Ports\Out\ClockPort;
use App\Ports\Out\EmployeeFinance\EmployeeDebtReaderPort;
use App\Ports\Out\EmployeeFinance\EmployeeDebtWriterPort;
use App\Ports\Out\TransactionManagerPort;
use App\Ports\Out\UuidPort;
use InvalidArgumentException;
use Throwable;

class PayEmployeeDebtHandler
{
    public function __construct(
        private EmployeeDebtReaderPort $debtReader,
        private EmployeeDebtWriterPort $debtWriter,
        private ClockPort $clockPort,
        private UuidPort $uuidPort,
        private TransactionManagerPort $transactionManager
    ) {
    }

    public function handle(string $debtId, int $paymentAmount, ?string $notes = null): string
    {
        $this->transactionManager->begin();

        try {
            $debt = $this->debtReader->findById($debtId);
            
            if (!$debt) {
                throw new InvalidArgumentException("Data hutang karyawan tidak ditemukan.");
            }

            $paymentId = $this->uuidPort->generate();
            $amount = Money::fromInt($paymentAmount);
            $paymentDate = $this->clockPort->now();

            $payment = new DebtPayment($paymentId, $amount, $paymentDate, $notes);

            $debt->pay($payment);

            $this->debtWriter->save($debt);

            $this->transactionManager->commit();

            return $paymentId;
        } catch (Throwable $e) {
            $this->transactionManager->rollBack();
            throw $e;
        }
    }
}
