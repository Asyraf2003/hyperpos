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

                $hasServiceComponent = $item->serviceDetail() !== null;
                $storeStockCount = count($item->storeStockLines());
                $externalPurchaseCount = count($item->externalPurchaseLines());

                return [
                    'id' => $item->id(),
                    'line_no' => $item->lineNo(),
                    'type_label' => $this->typeLabel($item),
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

                    // hybrid read helpers
                    'has_service_component' => $hasServiceComponent,
                    'store_stock_count' => $storeStockCount,
                    'external_purchase_count' => $externalPurchaseCount,
                    'refund_stock_return_count' => $storeStockCount,
                    'refund_external_count' => $externalPurchaseCount,
                    'refund_money_possible' => (int) ($settlement['net_paid_rupiah'] ?? 0) > 0,
                    'refund_preview_label' => $this->refundPreviewLabel($storeStockCount, $externalPurchaseCount),

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

    private function typeLabel(WorkItem $item): string
    {
        return match ($item->transactionType()) {
            WorkItem::TYPE_STORE_STOCK_SALE_ONLY => 'Produk Toko',
            WorkItem::TYPE_SERVICE_ONLY => 'Service Only',
            WorkItem::TYPE_SERVICE_WITH_STORE_STOCK_PART => 'Service + Part Toko',
            WorkItem::TYPE_SERVICE_WITH_EXTERNAL_PURCHASE => 'Service + Part External',
            default => 'Line Nota',
        };
    }

    private function refundPreviewLabel(int $storeStockCount, int $externalPurchaseCount): string
    {
        if ($storeStockCount > 0 && $externalPurchaseCount > 0) {
            return 'Uang balik mungkin, stok toko kembali, external disederhanakan.';
        }

        if ($storeStockCount > 0) {
            return 'Uang balik mungkin dan stok toko kembali.';
        }

        if ($externalPurchaseCount > 0) {
            return 'Uang balik mungkin, external tidak memicu stok toko.';
        }

        return 'Refund sederhana mengikuti uang yang memang sudah masuk.';
    }
}
