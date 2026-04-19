<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Application\Note\UseCases\CorrectPaidServiceOnlySupportTrait;
use App\Application\Shared\DTO\Result;
use App\Core\Note\WorkItem\WorkItem;
use App\Core\Shared\Exceptions\DomainException;
use App\Ports\Out\AuditLogPort;
use App\Ports\Out\ClockPort;
use App\Ports\Out\Note\NoteReaderPort;
use App\Ports\Out\Payment\CustomerRefundReaderPort;
use App\Ports\Out\Payment\PaymentAllocationReaderPort;

final class FinalizePaidNoteCorrection
{
    use CorrectPaidServiceOnlySupportTrait;

    public function __construct(
        private readonly NoteReaderPort $notes,
        private readonly NoteCorrectionSnapshotBuilder $snapshots,
        private readonly PersistNoteMutationTimeline $timeline,
        private readonly PaymentAllocationReaderPort $allocations,
        private readonly CustomerRefundReaderPort $refunds,
        private readonly ClockPort $clock,
        private readonly AuditLogPort $audit,
    ) {
    }

    public function complete(
        string $noteId,
        int $lineNo,
        string $mutationType,
        string $actorId,
        string $reason,
        array $before,
        WorkItem $corrected,
        string $successMessage,
    ): Result {
        $afterNote = $this->notes->getById($noteId)
            ?? throw new DomainException('Note tidak ditemukan setelah correction.');

        $after = $this->snapshots->build($afterNote);
        $refundReq = $this->calculateRefundRequired(
            $this->allocations,
            $this->refunds,
            $noteId,
            $afterNote->totalRupiah(),
        );

        $this->timeline->record(
            $noteId,
            $mutationType,
            $actorId,
            'admin',
            $reason,
            $this->clock->now(),
            $before,
            $after,
            null,
            null,
            ['refund_required_rupiah' => $refundReq],
        );

        $this->audit->record($mutationType, $this->formatAuditPayload(
            $actorId,
            $noteId,
            $lineNo,
            $reason,
            $refundReq,
            $before,
            $after,
        ));

        return Result::success(
            $this->formatSuccessPayload($afterNote, $corrected, $refundReq),
            $successMessage,
        );
    }
}
