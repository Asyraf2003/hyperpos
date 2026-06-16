<?php

declare(strict_types=1);

namespace App\Core\Note\Revision;

use App\Core\Note\Revision\Concerns\NoteRevisionAccessors;
use App\Core\Note\Revision\Concerns\NoteRevisionValidation;
use DateTimeImmutable;

final class NoteRevision
{
    public const TAX_MODE_NONE = 'none';
    public const TAX_MODE_PERCENT = 'percent';
    public const TAX_MODE_FIXED = 'fixed';

    use NoteRevisionAccessors;
    use NoteRevisionValidation;

    /**
     * @param list<NoteRevisionLineSnapshot> $lines
     */
    private function __construct(
        private string $id,
        private string $noteRootId,
        private int $revisionNumber,
        private ?string $parentRevisionId,
        private ?string $createdByActorId,
        private ?string $reason,
        private string $customerName,
        private ?string $customerPhone,
        private DateTimeImmutable $transactionDate,
        private int $grandTotalRupiah,
        private array $lines,
        private DateTimeImmutable $createdAt,
        private int $subtotalBeforeNoteTaxRupiah,
        private ?string $noteTaxInput,
        private string $noteTaxMode,
        private ?int $noteTaxRateBasisPoints,
        private int $noteTaxAmountRupiah,
    ) {
    }

    /**
     * @param list<NoteRevisionLineSnapshot> $lines
     */
    public static function create(
        string $id,
        string $noteRootId,
        int $revisionNumber,
        ?string $parentRevisionId,
        ?string $createdByActorId,
        ?string $reason,
        string $customerName,
        ?string $customerPhone,
        DateTimeImmutable $transactionDate,
        int $grandTotalRupiah,
        array $lines,
        DateTimeImmutable $createdAt,
        ?int $subtotalBeforeNoteTaxRupiah = null,
        ?string $noteTaxInput = null,
        string $noteTaxMode = self::TAX_MODE_NONE,
        ?int $noteTaxRateBasisPoints = null,
        int $noteTaxAmountRupiah = 0,
    ): self {
        $id = trim($id);
        $noteRootId = trim($noteRootId);
        $customerName = trim($customerName);
        $subtotalBeforeNoteTaxRupiah ??= max($grandTotalRupiah - $noteTaxAmountRupiah, 0);

        self::assertValidState(
            $id,
            $noteRootId,
            $revisionNumber,
            $customerName,
            $grandTotalRupiah,
            $lines,
        );

        self::assertValidTaxSnapshot(
            $subtotalBeforeNoteTaxRupiah,
            $noteTaxMode,
            $noteTaxRateBasisPoints,
            $noteTaxAmountRupiah,
        );

        return new self(
            $id,
            $noteRootId,
            $revisionNumber,
            $parentRevisionId !== null && trim($parentRevisionId) !== '' ? trim($parentRevisionId) : null,
            $createdByActorId !== null && trim($createdByActorId) !== '' ? trim($createdByActorId) : null,
            $reason !== null && trim($reason) !== '' ? trim($reason) : null,
            $customerName,
            $customerPhone !== null && trim($customerPhone) !== '' ? trim($customerPhone) : null,
            $transactionDate,
            $grandTotalRupiah,
            array_values($lines),
            $createdAt,
            $subtotalBeforeNoteTaxRupiah,
            self::normalizeTaxInput($noteTaxInput),
            $noteTaxMode,
            $noteTaxRateBasisPoints,
            $noteTaxAmountRupiah,
        );
    }

    private static function normalizeTaxInput(?string $taxInput): ?string
    {
        if ($taxInput === null) {
            return null;
        }

        $taxInput = trim($taxInput);

        return $taxInput === '' ? null : $taxInput;
    }
}
