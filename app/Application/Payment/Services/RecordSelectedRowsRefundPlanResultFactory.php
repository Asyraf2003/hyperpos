<?php

declare(strict_types=1);

namespace App\Application\Payment\Services;

use App\Application\Payment\DTO\RecordedSelectedRowsRefundPlanResult;
use App\Application\Payment\DTO\SelectedRowsRefundPlan;
use App\Application\Shared\DTO\Result;

final class RecordSelectedRowsRefundPlanResultFactory
{
    /**
     * @param array<string, mixed> $processed
     */
    public function success(SelectedRowsRefundPlan $plan, array $processed, int $activeTotalRupiah): Result
    {
        return Result::success(
            (new RecordedSelectedRowsRefundPlanResult(
                $plan->noteId(),
                $processed['refund_ids'],
                $plan->selectedRowIds(),
                $plan->unpaidRowIds(),
                $plan->cancellableRowIds(),
                (int) $processed['allocation_count'],
                $plan->totalRefundRupiah(),
                $activeTotalRupiah,
            ))->toArray(),
            'Refund selected rows berhasil dicatat.',
        );
    }
}
