<?php

declare(strict_types=1);

namespace App\Core\Procurement\SupplierPayment;

use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;

trait SupplierPaymentValidation
{
    private static function assertValid(string $id, string $invId, Money $amt, string $status, ?string $path): void
    {
        if (trim($id) === '') throw new DomainException('ID wajib ada.');
        if (trim($invId) === '') throw new DomainException('Invoice ID wajib ada.');
        if (!$amt->greaterThan(Money::zero())) throw new DomainException('Amount harus > 0.');
        
        $path = self::normalizePath($path);
        if ($status === SupplierPayment::PROOF_STATUS_PENDING && $path !== null) throw new DomainException('Path harus kosong jika pending.');
        if ($status === SupplierPayment::PROOF_STATUS_UPLOADED && $path === null) throw new DomainException('Path wajib ada jika uploaded.');
    }

    private static function normalizePath(?string $path): ?string
    {
        if ($path === null) return null;
        $val = trim($path);
        return $val === '' ? null : $val;
    }
}
