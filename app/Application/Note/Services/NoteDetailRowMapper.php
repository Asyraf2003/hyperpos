<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Core\Note\WorkItem\WorkItem;

final class NoteDetailRowMapper
{
    public function __construct(
        private readonly WorkItemOperationalStatusResolver $statuses,
    ) {
    }

    /**
     * @param array<int, WorkItem> $rows
     * @param array<string, array<string, mixed>> $settlements
     * @return list<array<string, mixed>>
     */
    public function map(array $rows, array $settlements): array
    {
        return array_map(
            function (WorkItem $item) use ($settlements): array {
                $settlement = $settlements[$item->id()] ?? [
                    'allocated_rupiah' => 0,
                    'refunded_rupiah' => 0,
                    'net_paid_rupiah' => 0,
                    'outstanding_rupiah' => $item->subtotalRupiah()->amount(),
                    'settlement_label' => 'hutang',
                ];

                $refundedRupiah = (int) ($settlement['refunded_rupiah'] ?? 0);
                $outstandingRupiah = (int) ($settlement['outstanding_rupiah'] ?? $item->subtotalRupiah()->amount());
                $lineStatus = $this->statuses->resolve($outstandingRupiah, $refundedRupiah);

                return [
                    'id' => $item->id(),
                    'line_no' => $item->lineNo(),
                    'type_label' => $item->transactionType() === WorkItem::TYPE_STORE_STOCK_SALE_ONLY ? 'Produk' : 'Servis',
                    'transaction_type' => $item->transactionType(),
                    'can_correct_service_only' => $item->transactionType() === WorkItem::TYPE_SERVICE_ONLY,

                    // legacy/raw fields kept temporarily for transition
                    'status' => $item->status(),
                    'settlement_label' => (string) ($settlement['settlement_label'] ?? 'hutang'),

                    // monetary summary
                    'subtotal_rupiah' => $item->subtotalRupiah()->amount(),
                    'allocated_rupiah' => (int) ($settlement['allocated_rupiah'] ?? 0),
                    'refunded_rupiah' => $refundedRupiah,
                    'net_paid_rupiah' => (int) ($settlement['net_paid_rupiah'] ?? 0),
                    'outstanding_rupiah' => $outstandingRupiah,

                    // new line-centric operational fields
                    'line_status' => $lineStatus,
                    'can_edit' => $lineStatus === WorkItemOperationalStatusResolver::STATUS_OPEN,
                    'can_pay' => $lineStatus === WorkItemOperationalStatusResolver::STATUS_OPEN,
                    'can_refund' => $lineStatus === WorkItemOperationalStatusResolver::STATUS_CLOSE,
                    'can_view_detail' => true,
                ];
            },
            $rows
        );
    }
}
