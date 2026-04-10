<?php

declare(strict_types=1);

namespace App\Core\EmployeeFinance\Employee;

use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;
use InvalidArgumentException;

trait EmployeeValidation
{
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

    private function assertSalaryReductionHasReason(?Money $newAmount, ?string $reason): void
    {
        if (
            $this->defaultSalaryAmount !== null &&
            $newAmount !== null &&
            $this->defaultSalaryAmount->greaterThan($newAmount) &&
            empty(trim((string) $reason))
        ) {
            throw new DomainException('Penurunan nominal default gaji wajib menyertakan alasan.');
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
}
