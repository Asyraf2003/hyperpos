<?php

declare(strict_types=1);

namespace App\Core\EmployeeFinance\Employee;

use App\Core\Shared\ValueObjects\Money;
use App\Core\Shared\Exceptions\DomainException;
use InvalidArgumentException;

class Employee
{
    public function __construct(
        private string $id,
        private string $name,
        private ?string $phone,
        private Money $baseSalary,
        private PayPeriod $payPeriod,
        private EmployeeStatus $status
    ) {
        $this->validateName($name);
    }

    public static function register(
        string $id,
        string $name,
        ?string $phone,
        Money $baseSalary,
        PayPeriod $payPeriod
    ): self {
        return new self(
            $id,
            $name,
            $phone,
            $baseSalary,
            $payPeriod,
            EmployeeStatus::ACTIVE
        );
    }

    public function updateBaseSalary(Money $newSalary, ?string $reason = null): void
    {
        // Jika gaji lama LEBIH BESAR dari gaji baru (Penurunan Gaji)
        if ($this->baseSalary->greaterThan($newSalary) && empty(trim((string)$reason))) {
            throw new DomainException("Penurunan gaji pokok wajib menyertakan alasan.");
        }

        $this->baseSalary = $newSalary;
    }
    
    public function deactivate(): void
    {
        $this->status = EmployeeStatus::INACTIVE;
    }

    private function validateName(string $name): void
    {
        if (empty(trim($name))) {
            throw new InvalidArgumentException("Nama karyawan tidak boleh kosong.");
        }
    }

    public function getId(): string { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getPhone(): ?string { return $this->phone; }
    public function getBaseSalary(): Money { return $this->baseSalary; }
    public function getPayPeriod(): PayPeriod { return $this->payPeriod; }
    public function getStatus(): EmployeeStatus { return $this->status; }
}
