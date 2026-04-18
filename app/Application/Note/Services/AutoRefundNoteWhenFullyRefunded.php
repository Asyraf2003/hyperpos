<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Ports\Out\ClockPort;
use App\Ports\Out\Note\NoteReaderPort;
use App\Ports\Out\Note\NoteWriterPort;
use App\Ports\Out\Payment\CustomerRefundReaderPort;

final class AutoRefundNoteWhenFullyRefunded
{
    public function __construct(
        private readonly NoteReaderPort $notes,
        private readonly NoteWriterPort $noteWriter,
        private readonly NoteCorrectionSnapshotBuilder $snapshots,
        private readonly PersistNoteMutationTimeline $timeline,
        private readonly CustomerRefundReaderPort $refunds,
        private readonly ClockPort $clock,
    ) {
    }

    public function refundIfEligible(
        string $noteId,
        string $actorId,
        string $actorRole,
        string $reason,
        ?string $relatedCustomerPaymentId = null,
        ?string $relatedCustomerRefundId = null,
    ): void {
        $note = $this->notes->getById(trim($noteId));

        if ($note === null || $note->isRefunded() || ! $note->isClosed()) {
            return;
        }

        $totalRefunded = $this->refunds->getTotalRefundedAmountByNoteId($note->id())->amount();

        if ($totalRefunded < $note->totalRupiah()->amount()) {
            return;
        }

        $before = $this->snapshots->build($note);
        $occurredAt = $this->clock->now();

        $note->refund($actorId, $occurredAt);
        $this->noteWriter->updateOperationalState($note);

        $this->timeline->record(
            $note->id(),
            'note_refunded',
            trim($actorId),
            trim($actorRole),
            trim($reason),
            $occurredAt,
            $before,
            $this->snapshots->build($note),
            $relatedCustomerPaymentId,
            $relatedCustomerRefundId,
            ['total_refunded_rupiah' => $totalRefunded],
        );
    }
}
