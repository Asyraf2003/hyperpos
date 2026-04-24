<?php

declare(strict_types=1);

namespace App\Application\Payment\Services;

use App\Application\Note\Services\CancelSelectedRowsAndSyncActiveNoteTotal;
use App\Application\Note\Services\NoteHistoryProjectionService;
use App\Application\Payment\DTO\RecordedSelectedRowsRefundPlanResult;
use App\Application\Payment\DTO\SelectedRowsRefundPlan;
use App\Application\Shared\DTO\Result;
use App\Core\Shared\Exceptions\DomainException;
use App\Ports\Out\AuditLogPort;
use App\Ports\Out\TransactionManagerPort;
use Throwable;

final class RecordSelectedRowsRefundPlanTransaction
{
    public function __construct(
        private readonly RecordSelectedRowsRefundPlanBucketProcessor $buckets,
        private readonly CancelSelectedRowsAndSyncActiveNoteTotal $cancelRows,
        private readonly TransactionManagerPort $transactions,
        private readonly AuditLogPort $audit,
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

            $this->projection->syncNote($plan->noteId());
            $this->audit->record('selected_rows_refund_plan_recorded', [
                'note_id' => $plan->noteId(),
                'actor_id' => $actorId,
                'actor_role' => $actorRole,
                'selected_row_ids' => $plan->selectedRowIds(),
                'unpaid_row_ids' => $plan->unpaidRowIds(),
                'refund_ids' => $processed['refund_ids'],
                'allocation_count' => $processed['allocation_count'],
                'total_refund_rupiah' => $plan->totalRefundRupiah(),
            ]);

            $this->transactions->commit();

            $result = new RecordedSelectedRowsRefundPlanResult(
                $plan->noteId(),
                $processed['refund_ids'],
                $plan->selectedRowIds(),
                $plan->unpaidRowIds(),
                (int) $processed['allocation_count'],
                $plan->totalRefundRupiah(),
                (int) (($canceled->data()['active_total_rupiah'] ?? 0)),
            );

            return Result::success($result->toArray(), 'Refund selected rows berhasil dicatat.');
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
