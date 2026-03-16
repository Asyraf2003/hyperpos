<?php

declare(strict_types=1);

namespace App\Application\EmployeeFinance\UseCases;

use App\Core\Shared\ValueObjects\Money;
use App\Ports\Out\EmployeeFinance\EmployeeReaderPort;
use App\Ports\Out\EmployeeFinance\EmployeeWriterPort;
use App\Ports\Out\TransactionManagerPort;
use InvalidArgumentException;

class UpdateEmployeeBaseSalaryHandler
{
    public function __construct(
        private EmployeeReaderPort $employeeReader,
        private EmployeeWriterPort $employeeWriter,
        private TransactionManagerPort $transactionManager
    ) {
    }

    public function handle(string $employeeId, int $newSalaryAmount, ?string $reason = null): void
    {
        $this->transactionManager->execute(function () use ($employeeId, $newSalaryAmount, $reason) {
            $employee = $this->employeeReader->findById($employeeId);
            
            if (!$employee) {
                throw new InvalidArgumentException("Karyawan tidak ditemukan.");
            }

            $newSalary = Money::fromInt($newSalaryAmount);
            
            // Aturan Domain Exception untuk "penurunan wajib alasan" ada di dalam metode ini
            $employee->updateBaseSalary($newSalary, $reason);

            $this->employeeWriter->save($employee);
        });
    }
}
