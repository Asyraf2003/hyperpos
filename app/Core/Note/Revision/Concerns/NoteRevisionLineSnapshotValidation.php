<?php

declare(strict_types=1);

namespace App\Core\Note\Revision\Concerns;

use App\Core\Shared\Exceptions\DomainException;

trait NoteRevisionLineSnapshotValidation
{
    private static function assertValidState(
        string $id,
        string $noteRevisionId,
        int $lineNo,
        string $transactionType,
        string $status,
        int $subtotalRupiah,
        ?int $servicePriceRupiah,
    ): void {
        if ($id === '') {
            throw new DomainException('Id snapshot line revision wajib diisi.');
        }

        if ($noteRevisionId === '') {
            throw new DomainException('Note revision id pada snapshot line wajib diisi.');
        }

        if ($lineNo <= 0) {
            throw new DomainException('Line number snapshot revision wajib lebih dari nol.');
        }

        if ($transactionType === '') {
            throw new DomainException('Transaction type snapshot revision wajib diisi.');
        }

        if ($status === '') {
            throw new DomainException('Status snapshot revision wajib diisi.');
        }

        if ($subtotalRupiah < 0) {
            throw new DomainException('Subtotal snapshot revision tidak boleh negatif.');
        }

        if ($servicePriceRupiah !== null && $servicePriceRupiah < 0) {
            throw new DomainException('Harga service snapshot revision tidak boleh negatif.');
        }
    }
}
