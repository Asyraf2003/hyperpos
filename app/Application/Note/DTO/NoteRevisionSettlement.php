<?php

declare(strict_types=1);

namespace App\Application\Note\DTO;

use App\Core\Shared\Exceptions\DomainException;
use DateTimeImmutable;

final class NoteRevisionSettlement
{
    public const STATUS_UNDERPAID = 'underpaid';
    public const STATUS_PAID = 'paid';
    public const STATUS_OVERPAID_PENDING = 'overpaid_pending';

    private const VALID_STATUSES = [
        self::STATUS_UNDERPAID,
        self::STATUS_PAID,
        self::STATUS_OVERPAID_PENDING,
    ];

    private function __construct(
        public readonly string $id,
        public readonly string $noteRevisionId,
        public readonly string $noteRootId,
        public readonly int $grossTotalRupiah,
        public readonly int $carryForwardPaidRupiah,
        public readonly int $carryForwardRefundedRupiah,
        public readonly int $netPaidRupiah,
        public readonly int $outstandingRupiah,
        public readonly int $surplusRupiah,
        public readonly string $settlementStatus,
        public readonly DateTimeImmutable $createdAt,
    ) {
    }

    public static function create(
        string $id,
        string $noteRevisionId,
        string $noteRootId,
        int $grossTotalRupiah,
        int $carryForwardPaidRupiah,
        int $carryForwardRefundedRupiah,
        int $netPaidRupiah,
        int $outstandingRupiah,
        int $surplusRupiah,
        string $settlementStatus,
        DateTimeImmutable $createdAt,
    ): self {
        $id = trim($id);
        $noteRevisionId = trim($noteRevisionId);
        $noteRootId = trim($noteRootId);
        $settlementStatus = trim($settlementStatus);

        if ($id === '' || $noteRevisionId === '' || $noteRootId === '') {
            throw new DomainException('Revision settlement identity wajib diisi.');
        }

        foreach ([
            $grossTotalRupiah,
            $carryForwardPaidRupiah,
            $carryForwardRefundedRupiah,
            $netPaidRupiah,
            $outstandingRupiah,
            $surplusRupiah,
        ] as $amount) {
            if ($amount < 0) {
                throw new DomainException('Revision settlement amount tidak boleh negatif.');
            }
        }

        if (! in_array($settlementStatus, self::VALID_STATUSES, true)) {
            throw new DomainException('Revision settlement status tidak valid.');
        }

        if ($outstandingRupiah > 0 && $surplusRupiah > 0) {
            throw new DomainException('Revision settlement tidak boleh outstanding dan surplus bersamaan.');
        }

        return new self(
            $id,
            $noteRevisionId,
            $noteRootId,
            $grossTotalRupiah,
            $carryForwardPaidRupiah,
            $carryForwardRefundedRupiah,
            $netPaidRupiah,
            $outstandingRupiah,
            $surplusRupiah,
            $settlementStatus,
            $createdAt,
        );
    }
}
