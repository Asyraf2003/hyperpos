<?php

declare(strict_types=1);

namespace App\Application\Payment\Services;

use App\Application\Payment\DTO\SelectedRowsRefundPlan;
use App\Ports\Out\AuditLogPort;

final class RecordSelectedRowsRefundPlanAuditRecorder
{
    public function __construct(
        private readonly AuditLogPort $audit,
    ) {
    }

    public function record(
        SelectedRowsRefundPlan $plan,
        string $actorId,
        string $actorRole,
        array $processed,
        array $finalizedData,
    ): void {
        $this->audit->record('selected_rows_refund_plan_recorded', [
            'note_id' => $plan->noteId(),
            'actor_id' => $actorId,
            'actor_role' => $actorRole,
            'selected_row_ids' => $plan->selectedRowIds(),
            'unpaid_row_ids' => $plan->unpaidRowIds(),
            'refund_ids' => $processed['refund_ids'],
            'allocation_count' => $processed['allocation_count'],
            'total_refund_rupiah' => $plan->totalRefundRupiah(),
            'final_note_state' => $finalizedData['note_state'] ?? null,
        ]);
    }
}
