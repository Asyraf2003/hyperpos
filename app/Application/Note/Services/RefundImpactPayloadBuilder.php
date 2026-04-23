<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Core\Note\WorkItem\WorkItem;

final class RefundImpactPayloadBuilder
{
    public function __construct(
        private readonly RefundImpactProductLabelResolver $labels,
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
        $storeReturns = $this->mapStoreReturns($storeLines);
        $externalReturns = $this->mapExternalReturns($externalLines);

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

    private function mapStoreReturns(array $lines): array
    {
        return array_values(array_filter(array_map(function (mixed $line): ?array {
            $id = $this->stringFrom($line, ['id'], ['id']);
            $productId = $this->stringFrom($line, ['product_id', 'productId'], ['productId']);
            $qty = $this->intFrom($line, ['qty'], ['qty']);

            if ($id === '' || $productId === '' || $qty <= 0) {
                return null;
            }

            return [
                'source_line_id' => $id,
                'product_id' => $productId,
                'product_label' => $this->labels->resolve($productId),
                'qty' => $qty,
            ];
        }, $lines)));
    }

    private function mapExternalReturns(array $lines): array
    {
        return array_values(array_filter(array_map(function (mixed $line): ?array {
            $id = $this->stringFrom($line, ['id'], ['id']);
            $description = $this->stringFrom($line, ['cost_description', 'costDescription'], ['costDescription']);
            $qty = $this->intFrom($line, ['qty'], ['qty']);

            if ($id === '' || $description === '' || $qty <= 0) {
                return null;
            }

            $amount = $this->amountFrom($line, $qty);

            return [
                'source_line_id' => $id,
                'description' => $description,
                'qty' => $qty,
                'amount_rupiah' => $amount,
            ];
        }, $lines)));
    }

    private function stringFrom(mixed $source, array $keys, array $methods): string
    {
        foreach ($methods as $method) {
            if (is_object($source) && method_exists($source, $method)) {
                return trim((string) $source->{$method}());
            }
        }

        foreach ($keys as $key) {
            if (is_array($source) && array_key_exists($key, $source)) {
                return trim((string) $source[$key]);
            }
        }

        return '';
    }

    private function intFrom(mixed $source, array $keys, array $methods): int
    {
        foreach ($methods as $method) {
            if (is_object($source) && method_exists($source, $method)) {
                return (int) $source->{$method}();
            }
        }

        foreach ($keys as $key) {
            if (is_array($source) && array_key_exists($key, $source)) {
                return (int) $source[$key];
            }
        }

        return 0;
    }

    private function amountFrom(mixed $source, int $qty): int
    {
        if (is_object($source) && method_exists($source, 'lineTotalRupiah')) {
            return (int) $source->lineTotalRupiah()->amount();
        }

        if (is_array($source) && array_key_exists('line_total_rupiah', $source)) {
            return (int) $source['line_total_rupiah'];
        }

        if (is_object($source) && method_exists($source, 'unitCostRupiah')) {
            return (int) $source->unitCostRupiah()->amount() * $qty;
        }

        if (is_array($source) && array_key_exists('unit_cost_rupiah', $source)) {
            return (int) $source['unit_cost_rupiah'] * $qty;
        }

        return 0;
    }
}
