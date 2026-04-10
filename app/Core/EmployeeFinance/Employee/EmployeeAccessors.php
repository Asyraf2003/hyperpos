<?php

declare(strict_types=1);

namespace App\Core\EmployeeFinance\Employee;

use App\Core\Shared\ValueObjects\Money;
use DateTimeImmutable;

trait EmployeeAccessors
{
    public function getId(): string
    {
        return $this->id;
    }

    public function getEmployeeName(): string
    {
        return $this->employeeName;
    }

    public function getName(): string
    {
        return $this->employeeName;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function getDefaultSalaryAmount(): ?Money
    {
        return $this->defaultSalaryAmount;
    }

    public function getBaseSalary(): Money
    {
        return $this->defaultSalaryAmount ?? Money::fromInt(0);
    }

    public function getSalaryBasisType(): PayPeriod
    {
        return $this->salaryBasisType;
    }

    public function getPayPeriod(): PayPeriod
    {
        return $this->salaryBasisType;
    }

    public function getEmploymentStatus(): EmployeeStatus
    {
        return $this->employmentStatus;
    }

    public function getStatus(): EmployeeStatus
    {
        return $this->employmentStatus;
    }

    public function getStartedAt(): ?DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function getEndedAt(): ?DateTimeImmutable
    {
        return $this->endedAt;
    }
}
