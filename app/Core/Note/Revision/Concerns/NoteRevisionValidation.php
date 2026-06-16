<?php

declare(strict_types=1);

namespace App\Core\Note\Revision\Concerns;

use App\Core\Note\Revision\NoteRevision;
use App\Core\Note\Revision\NoteRevisionLineSnapshot;
use App\Core\Shared\Exceptions\DomainException;

trait NoteRevisionValidation
{
    /**
     * @param list<NoteRevisionLineSnapshot> $lines
     */
    private static function assertValidState(
        string $id,
        string $noteRootId,
        int $revisionNumber,
        string $customerName,
        int $grandTotalRupiah,
        array $lines,
    ): void {
        if ($id === '') {
            throw new DomainException('Note revision id wajib diisi.');
        }

        if ($noteRootId === '') {
            throw new DomainException('Note root id wajib diisi.');
        }

        if ($revisionNumber <= 0) {
            throw new DomainException('Revision number wajib lebih dari nol.');
        }

        if ($customerName === '') {
            throw new DomainException('Customer name revision wajib diisi.');
        }

        if ($grandTotalRupiah < 0) {
            throw new DomainException('Grand total revision tidak boleh negatif.');
        }

        foreach ($lines as $line) {
            if (! $line instanceof NoteRevisionLineSnapshot) {
                throw new DomainException('Semua line revision wajib berupa NoteRevisionLineSnapshot.');
            }

            if ($line->noteRevisionId() !== $id) {
                throw new DomainException('Semua line revision wajib belong ke note revision yang sama.');
            }
        }
    }

    private static function assertValidTaxSnapshot(
        int $subtotalBeforeNoteTaxRupiah,
        string $noteTaxMode,
        ?int $noteTaxRateBasisPoints,
        int $noteTaxAmountRupiah,
    ): void {
        if ($subtotalBeforeNoteTaxRupiah < 0) {
            throw new DomainException('Subtotal sebelum pajak revision tidak boleh negatif.');
        }

        if ($noteTaxAmountRupiah < 0) {
            throw new DomainException('Pajak revision tidak boleh negatif.');
        }

        if (! in_array($noteTaxMode, [NoteRevision::TAX_MODE_NONE, NoteRevision::TAX_MODE_PERCENT, NoteRevision::TAX_MODE_FIXED], true)) {
            throw new DomainException('Tax mode revision tidak valid.');
        }

        if ($noteTaxMode === NoteRevision::TAX_MODE_PERCENT && ($noteTaxRateBasisPoints === null || $noteTaxRateBasisPoints < 0)) {
            throw new DomainException('Tax rate basis points revision tidak valid.');
        }

        if ($noteTaxMode !== NoteRevision::TAX_MODE_PERCENT && $noteTaxRateBasisPoints !== null) {
            throw new DomainException('Tax rate basis points revision hanya boleh ada untuk mode percent.');
        }

        if ($noteTaxMode === NoteRevision::TAX_MODE_NONE && $noteTaxAmountRupiah !== 0) {
            throw new DomainException('Pajak revision harus nol ketika tax mode none.');
        }
    }
}
