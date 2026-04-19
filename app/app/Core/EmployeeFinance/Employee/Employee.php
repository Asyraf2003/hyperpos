<?php

declare(strict_types=1);

namespace App\Core\EmployeeFinance\Employee;

use App\Core\Shared\ValueObjects\Money;
use DateTimeImmutable;

class Employee
{
    use EmployeeValidation;
    use EmployeeAccessors;

    public function __construct(
        private string $id,
        private string $employeeName,
        private ?string $phone,
        private ?Money $defaultSalaryAmount,
        private PayPeriod $salaryBasisType,
        private EmployeeStatus $employmentStatus,
        private ?DateTimeImmutable $startedAt = null,
        private ?DateTimeImmutable $endedAt = null,
    ) {
        $this->validateEmployeeName($employeeName);
        $this->validateDefaultSalaryAmount($defaultSalaryAmount);
    }

    public static function register(
        string $id,
        string $employeeName,
        ?string $phone,
        ?Money $defaultSalaryAmount,
        PayPeriod $salaryBasisType,
        ?DateTimeImmutable $startedAt = null,
        ?DateTimeImmutable $endedAt = null,
    ): self {
        return new self(
            $id,
            $employeeName,
            self::normalizePhone($phone),
            $defaultSalaryAmount,
            $salaryBasisType,
            self::resolveEmploymentStatus($endedAt),
            $startedAt,
            $endedAt,
        );
    }

    public function updateProfile(
        string $employeeName,
        ?string $phone,
        PayPeriod $salaryBasisType,
        ?DateTimeImmutable $startedAt = null,
        ?DateTimeImmutable $endedAt = null,
    ): void {
        $this->validateEmployeeName($employeeName);
        $this->employeeName = $employeeName;
        $this->phone = self::normalizePhone($phone);
        $this->salaryBasisType = $salaryBasisType;
        $this->startedAt = $startedAt;
        $this->endedAt = $endedAt;
        $this->employmentStatus = self::resolveEmploymentStatus($endedAt);
    }

    public function updateDefaultSalaryAmount(?Money $newAmount, ?string $reason = null): void
    {
        $this->validateDefaultSalaryAmount($newAmount);
        $this->assertSalaryReductionHasReason($newAmount, $reason);
        $this->defaultSalaryAmount = $newAmount;
    }

    public function updateBaseSalary(Money $newSalary, ?string $reason = null): void
    {
        $this->updateDefaultSalaryAmount($newSalary, $reason);
    }

    public function activate(): void
    {
        $this->employmentStatus = EmployeeStatus::ACTIVE;
    }

    public function deactivate(): void
    {
        $this->employmentStatus = EmployeeStatus::INACTIVE;
    }

    private static function resolveEmploymentStatus(?DateTimeImmutable $endedAt): EmployeeStatus
    {
        return $endedAt === null
            ? EmployeeStatus::ACTIVE
            : EmployeeStatus::INACTIVE;
    }
}
