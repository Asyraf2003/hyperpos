<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Core\Note\WorkItem\WorkItem;

final class NoteDetailRowMapper
{
    public function __construct(
        private readonly WorkItemOperationalStatusResolver $statuses,
        private readonly NoteDetailRowPresentationSupport $presentation,
    ) {
    }

    public function map(array $rows, array $settlements): array
    {
        return array_map(fn (WorkItem $item): array => $this->mapItem($item, $settlements), $rows);
    }

    private function mapItem(WorkItem $item, array $settlements): array
    {
        $settlement = $settlements[$item->id()] ?? [
            'allocated_rupiah' => 0,
            'refunded_rupiah' => 0,
            'net_paid_rupiah' => 0,
            'outstanding_rupiah' => $item->subtotalRupiah()->amount(),
            'settlement_label' => 'hutang',
        ];

        $refunded = (int) ($settlement['refunded_rupiah'] ?? 0);
        $outstanding = (int) ($settlement['outstanding_rupiah'] ?? $item->subtotalRupiah()->amount());
        $lineStatus = $this->statuses->resolve($outstanding, $refunded);
        $storeCount = count($item->storeStockLines());
        $externalCount = count($item->externalPurchaseLines());

        return [
            'id' => $item->id(),
            'line_no' => $item->lineNo(),
            'type_label' => $this->presentation->typeLabel($item),
            'transaction_type' => $item->transactionType(),
            'can_correct_service_only' => $item->transactionType() === WorkItem::TYPE_SERVICE_ONLY,
            'status' => $item->status(),
            'settlement_label' => (string) ($settlement['settlement_label'] ?? 'hutang'),
            'subtotal_rupiah' => $item->subtotalRupiah()->amount(),
            'allocated_rupiah' => (int) ($settlement['allocated_rupiah'] ?? 0),
            'refunded_rupiah' => $refunded,
            'net_paid_rupiah' => (int) ($settlement['net_paid_rupiah'] ?? 0),
            'outstanding_rupiah' => $outstanding,
            'has_service_component' => $item->serviceDetail() !== null,
            'store_stock_count' => $storeCount,
            'external_purchase_count' => $externalCount,
            'refund_stock_return_count' => $storeCount,
            'refund_external_count' => $externalCount,
            'refund_money_possible' => (int) ($settlement['net_paid_rupiah'] ?? 0) > 0,
            'refund_preview_label' => $this->presentation->refundPreviewLabel($storeCount, $externalCount),
            'line_status' => $lineStatus,
            'can_edit' => $lineStatus === WorkItemOperationalStatusResolver::STATUS_OPEN,
            'can_pay' => $lineStatus === WorkItemOperationalStatusResolver::STATUS_OPEN,
            'can_refund' => $lineStatus === WorkItemOperationalStatusResolver::STATUS_CLOSE,
            'can_view_detail' => true,
        ];
    }
}
