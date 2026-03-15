<?php

declare(strict_types=1);

namespace App\Application\Note\UseCases;

use App\Application\Note\Policies\NotePaidStatusPolicy;
use App\Application\Note\Services\NoteCorrectionSnapshotBuilder;
use App\Application\Note\Services\WorkItemStatusTransitionService;
use App\Application\Shared\DTO\Result;
use App\Core\Shared\Exceptions\DomainException;
use App\Ports\Out\AuditLogPort;
use App\Ports\Out\Note\NoteReaderPort;
use App\Ports\Out\Note\WorkItemWriterPort;
use App\Ports\Out\TransactionManagerPort;
use Throwable;

final class CorrectPaidWorkItemStatusHandler
{
    public function __construct(
        private readonly NoteReaderPort $notes,
        private readonly WorkItemWriterPort $workItems,
        private readonly TransactionManagerPort $transactions,
        private readonly WorkItemStatusTransitionService $transition,
        private readonly NotePaidStatusPolicy $paidStatus,
        private readonly NoteCorrectionSnapshotBuilder $snapshots,
        private readonly AuditLogPort $audit,
    ) {
    }

    public function handle(
        string $noteId,
        int $lineNo,
        string $targetStatus,
        string $reason,
        string $performedByActorId,
    ): Result {
        $started = false;

        try {
            if ($lineNo <= 0) {
                throw new DomainException('Line number harus > 0.');
            }

            if (trim($reason) === '') {
                return Result::failure(
                    'Alasan correction wajib diisi.',
                    ['correction' => ['AUDIT_REASON_REQUIRED']],
                );
            }

            if (trim($performedByActorId) === '') {
                throw new DomainException('Actor correction wajib ada.');
            }

            $this->transactions->begin();
            $started = true;

            $note = $this->notes->getById(trim($noteId))
                ?? throw new DomainException('Note tidak ditemukan.');

            $this->paidStatus->assertPaidForCorrection($note);

            $before = $this->snapshots->build($note);

            $workItem = $this->transition->findAndApply($note, $lineNo, trim($targetStatus));

            $this->workItems->updateStatus($workItem);

            $afterNote = $this->notes->getById($note->id())
                ?? throw new DomainException('Note tidak ditemukan setelah correction.');

            $after = $this->snapshots->build($afterNote);

            $this->audit->record('paid_work_item_status_corrected', [
                'performed_by_actor_id' => trim($performedByActorId),
                'note_id' => $note->id(),
                'line_no' => $lineNo,
                'target_status' => $workItem->status(),
                'reason' => trim($reason),
                'before' => $before,
                'after' => $after,
            ]);

            $this->transactions->commit();

            return Result::success(
                [
                    'note' => [
                        'id' => $afterNote->id(),
                    ],
                    'work_item' => [
                        'id' => $workItem->id(),
                        'status' => $workItem->status(),
                    ],
                ],
                'Correction status work item berhasil disimpan.',
            );
        } catch (DomainException $e) {
            if ($started) {
                $this->transactions->rollBack();
            }

            return Result::failure(
                $e->getMessage(),
                ['work_item' => ['INVALID_WORK_ITEM_STATE']],
            );
        } catch (Throwable $e) {
            if ($started) {
                $this->transactions->rollBack();
            }

            throw $e;
        }
    }
}
