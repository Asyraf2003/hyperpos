<?php

declare(strict_types=1);

namespace App\Application\EmployeeFinance\UseCases;

use App\Core\EmployeeFinance\EmployeeDebt\EmployeeDebt;
use App\Core\Shared\ValueObjects\Money;
use App\Ports\Out\EmployeeFinance\EmployeeReaderPort;
use App\Ports\Out\EmployeeFinance\EmployeeDebtWriterPort;
use App\Ports\Out\TransactionManagerPort;
use App\Ports\Out\UuidPort;
use InvalidArgumentException;
use Throwable;

class RecordEmployeeDebtHandler
{
    public function __construct(
        private EmployeeReaderPort $employeeReader,
        private EmployeeDebtWriterPort $debtWriter,
        private UuidPort $uuidPort,
        private TransactionManagerPort $transactionManager
    ) {
    }

    public function handle(string $employeeId, int $debtAmount, ?string $notes = null): string
    {
        $this->transactionManager->begin();

        try {
            // Validasi keberadaan karyawan sebelum memberikan hutang
            $employee = $this->employeeReader->findById($employeeId);
            
            if (!$employee) {
                throw new InvalidArgumentException("Karyawan tidak ditemukan.");
            }

            $id = $this->uuidPort->generate();
            $amount = Money::fromInt($debtAmount);

            $debt = EmployeeDebt::record($id, $employeeId, $amount, $notes);

            $this->debtWriter->save($debt);

            $this->transactionManager->commit();

            return $id;
        } catch (Throwable $e) {
            $this->transactionManager->rollBack();
            throw $e;
        }
    }
}
