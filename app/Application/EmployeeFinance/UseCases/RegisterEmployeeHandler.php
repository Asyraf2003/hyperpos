<?php

declare(strict_types=1);

namespace App\Application\EmployeeFinance\UseCases;

use App\Application\EmployeeFinance\Context\EmployeeChangeContext;
use App\Core\EmployeeFinance\Employee\Employee;
use App\Core\EmployeeFinance\Employee\PayPeriod;
use App\Core\Shared\ValueObjects\Money;
use App\Ports\Out\EmployeeFinance\EmployeeWriterPort;
use App\Ports\Out\TransactionManagerPort;
use App\Ports\Out\UuidPort;
use DateTimeImmutable;
use Throwable;

final class RegisterEmployeeHandler
{
    public function __construct(
        private EmployeeWriterPort $employeeWriter,
        private UuidPort $uuidPort,
        private TransactionManagerPort $transactionManager,
        private EmployeeChangeContext $changeContext,
    ) {
    }

    public function handle(
        string $employeeName,
        ?string $phone,
        ?int $defaultSalaryAmount,
        string $salaryBasisType,
        ?string $startedAt = null,
        ?string $endedAt = null,
    ): string {
        $this->transactionManager->begin();

        try {
            $id = $this->uuidPort->generate();

            $this->changeContext->set(
                null,
                null,
                'admin_web',
                null,
            );

            $employee = Employee::register(
                $id,
                $employeeName,
                $phone,
                $this->toNullableMoney($defaultSalaryAmount),
                PayPeriod::from($salaryBasisType),
                $this->parseOptionalDate($startedAt),
                $this->parseOptionalDate($endedAt),
            );

            $this->employeeWriter->save($employee);

            $this->transactionManager->commit();

            return $id;
        } catch (Throwable $e) {
            $this->changeContext->clear();
            $this->transactionManager->rollBack();
            throw $e;
        }
    }

    private function toNullableMoney(?int $amount): ?Money
    {
        if ($amount === null || $amount <= 0) {
            return null;
        }

        return Money::fromInt($amount);
    }

    private function parseOptionalDate(?string $value): ?DateTimeImmutable
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        return new DateTimeImmutable($value);
    }
}
