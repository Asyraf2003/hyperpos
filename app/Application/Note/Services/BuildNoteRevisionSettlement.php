<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Application\Note\DTO\NoteRevisionSettlement;
use App\Core\Shared\Exceptions\DomainException;
use DateTimeImmutable;

final class BuildNoteRevisionSettlement
{
    public function build(
        string $id,
        string $noteRevisionId,
        string $noteRootId,
        int $grossTotalRupiah,
        int $carryForwardPaidRupiah,
        int $carryForwardRefundedRupiah,
        DateTimeImmutable $createdAt,
    ): NoteRevisionSettlement {
        foreach ([$grossTotalRupiah, $carryForwardPaidRupiah, $carryForwardRefundedRupiah] as $amount) {
            if ($amount < 0) {
                throw new DomainException('Revision settlement input tidak boleh negatif.');
            }
        }

        $netPaidRupiah = max($carryForwardPaidRupiah - $carryForwardRefundedRupiah, 0);
        $outstandingRupiah = max($grossTotalRupiah - $netPaidRupiah, 0);
        $surplusRupiah = max($netPaidRupiah - $grossTotalRupiah, 0);
        $status = $this->status($outstandingRupiah, $surplusRupiah);

        return NoteRevisionSettlement::create(
            $id,
            $noteRevisionId,
            $noteRootId,
            $grossTotalRupiah,
            $carryForwardPaidRupiah,
            $carryForwardRefundedRupiah,
            $netPaidRupiah,
            $outstandingRupiah,
            $surplusRupiah,
            $status,
            $createdAt,
        );
    }

    private function status(int $outstandingRupiah, int $surplusRupiah): string
    {
        if ($surplusRupiah > 0) {
            return NoteRevisionSettlement::STATUS_OVERPAID_PENDING;
        }

        if ($outstandingRupiah > 0) {
            return NoteRevisionSettlement::STATUS_UNDERPAID;
        }

        return NoteRevisionSettlement::STATUS_PAID;
    }
}
