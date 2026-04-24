<?php

declare(strict_types=1);

namespace App\Application\Payment\DTO;

final class RecordedSelectedRowsRefundPlanResult
{
    /**
     * @param list<string> $refundIds
     * @param list<string> $selectedRowIds
     * @param list<string> $unpaidRowIds
     */
    public function __construct(
        private readonly string $noteId,
        private readonly array $refundIds,
        private readonly array $selectedRowIds,
        private readonly array $unpaidRowIds,
        private readonly int $allocationCount,
        private readonly int $totalRefundRupiah,
        private readonly int $activeTotalRupiah,
    ) {
    }

    public function toArray(): array
    {
        return [
            'note_id' => $this->noteId,
            'refund_ids' => $this->refundIds,
            'selected_row_ids' => $this->selectedRowIds,
            'unpaid_row_ids' => $this->unpaidRowIds,
            'allocation_count' => $this->allocationCount,
            'total_refund_rupiah' => $this->totalRefundRupiah,
            'active_total_rupiah' => $this->activeTotalRupiah,
        ];
    }
}
