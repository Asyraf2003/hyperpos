<?php

declare(strict_types=1);

namespace App\Core\EmployeeFinance\Employee;

use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;
use DateTimeImmutable;
use InvalidArgumentException;

class Employee
{
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
            EmployeeStatus::ACTIVE,
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
    }

    public function updateDefaultSalaryAmount(?Money $newAmount, ?string $reason = null): void
    {
        $this->validateDefaultSalaryAmount($newAmount);

        if (
            $this->defaultSalaryAmount !== null &&
            $newAmount !== null &&
            $this->defaultSalaryAmount->greaterThan($newAmount) &&
            empty(trim((string) $reason))
        ) {
            throw new DomainException('Penurunan nominal default gaji wajib menyertakan alasan.');
        }

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

    private function validateEmployeeName(string $employeeName): void
    {
        if (trim($employeeName) === '') {
            throw new InvalidArgumentException('Nama karyawan tidak boleh kosong.');
        }
    }

    private function validateDefaultSalaryAmount(?Money $defaultSalaryAmount): void
    {
        if ($defaultSalaryAmount === null) {
            return;
        }

        if ($defaultSalaryAmount->isZero() || $defaultSalaryAmount->isNegative()) {
            throw new InvalidArgumentException('Nominal default gaji harus lebih dari nol.');
        }
    }

    private static function normalizePhone(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        $normalized = trim($phone);

        return $normalized === '' ? null : $normalized;
    }

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
