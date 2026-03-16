<?php

declare(strict_types=1);

namespace App\Application\EmployeeFinance\UseCases;

use App\Core\EmployeeFinance\Employee\Employee;
use App\Core\EmployeeFinance\Employee\PayPeriod;
use App\Core\Shared\ValueObjects\Money;
use App\Ports\Out\EmployeeFinance\EmployeeWriterPort;
use App\Ports\Out\TransactionManagerPort;
use App\Ports\Out\UuidPort;
use Throwable;

class RegisterEmployeeHandler
{
    public function __construct(
        private EmployeeWriterPort $employeeWriter,
        private UuidPort $uuidPort,
        private TransactionManagerPort $transactionManager
    ) {
    }

    public function handle(
        string $name,
        ?string $phone,
        int $baseSalaryAmount,
        string $payPeriodValue
    ): string {
        $this->transactionManager->begin();

        try {
            $id = $this->uuidPort->generate();
            $baseSalary = Money::fromInt($baseSalaryAmount);
            $payPeriod = PayPeriod::from($payPeriodValue);

            $employee = Employee::register($id, $name, $phone, $baseSalary, $payPeriod);

            $this->employeeWriter->save($employee);

            $this->transactionManager->commit();

            return $id;
        } catch (Throwable $e) {
            $this->transactionManager->rollBack();
            throw $e;
        }
    }
}
