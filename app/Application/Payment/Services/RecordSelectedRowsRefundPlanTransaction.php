<?php

declare(strict_types=1);

namespace App\Application\Payment\Services;

use App\Application\Note\Services\CancelSelectedRowsAndSyncActiveNoteTotal;
use App\Application\Note\Services\FinalizeRefundedNoteFromActiveRows;
use App\Application\Note\Services\NoteHistoryProjectionService;
use App\Application\Payment\DTO\RecordedSelectedRowsRefundPlanResult;
use App\Application\Payment\DTO\SelectedRowsRefundPlan;
use App\Application\Shared\DTO\Result;
use App\Core\Shared\Exceptions\DomainException;
use App\Ports\Out\TransactionManagerPort;
use Throwable;

final class RecordSelectedRowsRefundPlanTransaction
{
    public function __construct(
        private readonly RecordSelectedRowsRefundPlanBucketProcessor $buckets,
        private readonly CancelSelectedRowsAndSyncActiveNoteTotal $cancelRows,
        private readonly FinalizeRefundedNoteFromActiveRows $finalizeRefunded,
        private readonly TransactionManagerPort $transactions,
        private readonly RecordSelectedRowsRefundPlanAuditRecorder $audit,
        private readonly NoteHistoryProjectionService $projection,
    ) {
    }

    public function run(
        SelectedRowsRefundPlan $plan,
        string $refundedAt,
        string $reason,
        string $actorId,
        string $actorRole,
    ): Result {
        $started = false;

        try {
            $this->transactions->begin();
            $started = true;

            $processed = $this->buckets->process($plan, $refundedAt, $reason);
            $canceled = $this->cancelRows->execute($plan->noteId(), $plan->selectedRowIds(), $actorId, $actorRole, $reason);

            if ($canceled->isFailure()) {
                throw new DomainException($canceled->message() ?? 'Gagal membatalkan line refund.');
            }

            $finalized = $this->finalizeRefunded->execute($plan->noteId(), $actorId, $actorRole, $reason);

            if ($finalized->isFailure()) {
                throw new DomainException($finalized->message() ?? 'Gagal finalisasi note refund.');
            }

            $this->projection->syncNote($plan->noteId());
            $this->audit->record($plan, $actorId, $actorRole, $processed, $finalized->data());

            $this->transactions->commit();

            return Result::success(
                (new RecordedSelectedRowsRefundPlanResult(
                    $plan->noteId(),
                    $processed['refund_ids'],
                    $plan->selectedRowIds(),
                    $plan->unpaidRowIds(),
                    (int) $processed['allocation_count'],
                    $plan->totalRefundRupiah(),
                    (int) ($canceled->data()['active_total_rupiah'] ?? 0),
                ))->toArray(),
                'Refund selected rows berhasil dicatat.',
            );
        } catch (DomainException $e) {
            if ($started) {
                $this->transactions->rollBack();
            }

            return Result::failure($e->getMessage(), ['refund' => ['SELECTED_ROWS_REFUND_FAILED']]);
        } catch (Throwable $e) {
            if ($started) {
                $this->transactions->rollBack();
            }

            throw $e;
        }
    }
}
