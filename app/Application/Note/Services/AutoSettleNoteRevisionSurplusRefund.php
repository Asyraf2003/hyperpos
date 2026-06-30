<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Application\Note\DTO\NoteRevisionSettlement;
use DateTimeImmutable;

final class AutoSettleNoteRevisionSurplusRefund
{
    public function __construct(
        private readonly AutoSettleNoteRevisionSurplusRefundDueRecorder $dueRecorder,
        private readonly AutoSettleNoteRevisionSurplusRefundPaymentRecorder $paymentRecorder,
    ) {
    }

    public function settle(
        NoteRevisionSettlement $settlement,
        ?string $actorId,
        string $reason,
        DateTimeImmutable $effectiveAt,
    ): void {
        if ($settlement->settlementStatus !== NoteRevisionSettlement::STATUS_OVERPAID_PENDING) {
            return;
        }

        if ($settlement->surplusRupiah <= 0) {
            return;
        }

        $actorId = $this->actorId($actorId);
        $reason = $this->reason($reason);
        $correlationId = sprintf('auto-surplus-refund:%s', $settlement->id);

        $disposition = $this->dueRecorder->record(
            $settlement,
            $actorId,
            $reason,
            $effectiveAt,
            $correlationId,
        );

        $this->paymentRecorder->record(
            $settlement,
            $disposition,
            $actorId,
            $reason,
            $effectiveAt,
            $correlationId,
        );
    }

    private function actorId(?string $actorId): string
    {
        $actorId = trim((string) $actorId);

        return $actorId === '' ? 'system-note-revision-auto-surplus-refund' : $actorId;
    }

    private function reason(string $reason): string
    {
        $reason = trim($reason);

        return $reason === ''
            ? 'Auto refund due and refund paid from downward note revision surplus.'
            : $reason;
    }
}
