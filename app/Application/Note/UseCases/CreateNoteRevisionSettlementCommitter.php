<?php

declare(strict_types=1);

namespace App\Application\Note\UseCases;

use App\Application\Note\Services\AutoSettleNoteRevisionSurplusRefund;
use App\Application\Note\Services\BuildCreateNoteRevisionSettlement;
use App\Core\Note\Revision\NoteRevision;
use DateTimeImmutable;

final class CreateNoteRevisionSettlementCommitter
{
    public function __construct(
        private readonly BuildCreateNoteRevisionSettlement $settlements,
        private readonly CreateNoteRevisionCommitter $committer,
        private readonly AutoSettleNoteRevisionSurplusRefund $autoSurplusRefund,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function commit(
        string $revisionId,
        string $noteRootId,
        string $parentRevisionId,
        int $grossTotalRupiah,
        ?string $actorId,
        string $reason,
        NoteRevision $revision,
        array $payload,
        DateTimeImmutable $createdAt,
    ): CreateNoteRevisionResult {
        $settlement = $this->settlements->build(
            sprintf('%s-settlement', $revisionId),
            $revisionId,
            $noteRootId,
            $grossTotalRupiah,
            $createdAt,
        );

        $result = $this->committer->commit(
            $noteRootId,
            $parentRevisionId,
            $actorId,
            $reason,
            $revision,
            $settlement,
        );

        $this->autoSurplusRefund->settle(
            $settlement,
            $actorId,
            $reason,
            $this->effectiveAt($payload, $createdAt),
        );

        return $result;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function effectiveAt(array $payload, DateTimeImmutable $fallback): DateTimeImmutable
    {
        $date = trim((string) ($payload['note']['transaction_date'] ?? ''));

        if ($date === '') {
            return $fallback;
        }

        return new DateTimeImmutable($date);
    }
}
