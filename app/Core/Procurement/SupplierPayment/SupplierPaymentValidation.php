<?php

declare(strict_types=1);

namespace App\Core\Procurement\SupplierPayment;

use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;

trait SupplierPaymentValidation
{
    private static function assertValid(string $id, string $invId, Money $amt, string $status, ?string $path): void
    {
        if (trim($id) === '') {
            throw new DomainException('ID wajib ada.');
        }

        if (trim($invId) === '') {
            throw new DomainException('Invoice ID wajib ada.');
        }

        if (! $amt->greaterThan(Money::zero())) {
            throw new DomainException('Amount harus > 0.');
        }

        $normalizedStatus = trim($status);
        $normalizedPath = self::normalizePath($path);

        if (! in_array($normalizedStatus, [
            SupplierPayment::PROOF_STATUS_PENDING,
            SupplierPayment::PROOF_STATUS_UPLOADED,
        ], true)) {
            throw new DomainException('Status bukti pembayaran supplier tidak valid.');
        }

        if ($normalizedStatus === SupplierPayment::PROOF_STATUS_PENDING && $normalizedPath !== null) {
            throw new DomainException('Path harus kosong jika pending.');
        }

        // uploaded boleh tanpa proof_storage_path karena source of truth bukti
        // pembayaran pindah ke tabel attachment terpisah.
    }

    private static function normalizePath(?string $path): ?string
    {
        if ($path === null) {
            return null;
        }

        $val = trim($path);

        return $val === '' ? null : $val;
    }
}
