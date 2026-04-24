<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Core\Note\WorkItem\WorkItem;

final class RefundImpactPayloadBuilder
{
    public function __construct(
        private readonly RefundImpactReturnsMapper $returns,
    ) {
    }

    public function fromWorkItem(WorkItem $item, int $refundAmountRupiah): array
    {
        return $this->build(
            $refundAmountRupiah,
            $item->storeStockLines(),
            $item->externalPurchaseLines(),
        );
    }

    public function fromRevisionPayload(array $payload, int $refundAmountRupiah): array
    {
        $storeLines = is_array($payload['store_stock_lines'] ?? null) ? $payload['store_stock_lines'] : [];
        $externalLines = is_array($payload['external_purchase_lines'] ?? null) ? $payload['external_purchase_lines'] : [];

        return $this->build($refundAmountRupiah, $storeLines, $externalLines);
    }

    private function build(int $refundAmountRupiah, array $storeLines, array $externalLines): array
    {
        $storeReturns = $this->returns->mapStoreReturns($storeLines);
        $externalReturns = $this->returns->mapExternalReturns($externalLines);

        return [
            'refund_amount_rupiah' => max($refundAmountRupiah, 0),
            'store_returns' => $storeReturns,
            'external_returns' => $externalReturns,
            'effect_summary' => [
                'stock_store_return_count' => array_sum(array_column($storeReturns, 'qty')),
                'external_item_count' => count($externalReturns),
                'line_net_effect_after_refund_label' => 'Line ini menjadi netral setelah refund.',
            ],
        ];
    }
}
