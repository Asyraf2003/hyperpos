<?php

declare(strict_types=1);

namespace App\Application\Note\UseCases;

use App\Application\Note\DTO\NoteRevisionSurplusDisposition;
use App\Application\Note\DTO\NoteRevisionSurplusPending;
use App\Ports\Out\ClockPort;
use App\Ports\Out\UuidPort;

final class CreateNoteRevisionSurplusRefundDueDispositionFactory
{
    public function __construct(
        private readonly UuidPort $uuid,
        private readonly ClockPort $clock,
    ) {
    }

    public function create(
        CreateNoteRevisionSurplusRefundDueCommand $command,
        NoteRevisionSurplusPending $pending,
    ): NoteRevisionSurplusDisposition {
        $occurredAt = $command->occurredAt ?? $this->clock->now();

        return NoteRevisionSurplusDisposition::create(
            $this->uuid->generate(),
            $pending->noteRevisionSettlementId,
            $pending->noteRootId,
            $pending->noteRevisionId,
            NoteRevisionSurplusDisposition::TYPE_REFUND_DUE,
            $command->amountRupiah,
            $pending->unresolvedPendingRupiah,
            $pending->unresolvedPendingRupiah - $command->amountRupiah,
            NoteRevisionSurplusDisposition::STATUS_ACTIVE,
            $occurredAt,
            $this->clock->now(),
            $this->uuid->generate(),
        );
    }
}
