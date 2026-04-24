<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

final class RefundImpactReturnsMapper
{
    public function __construct(
        private readonly RefundImpactProductLabelResolver $labels,
        private readonly RefundImpactSourceValueReader $values,
    ) {
    }

    public function mapStoreReturns(array $lines): array
    {
        return array_values(array_filter(array_map(function (mixed $line): ?array {
            $id = $this->values->stringFrom($line, ['id'], ['id']);
            $productId = $this->values->stringFrom($line, ['product_id', 'productId'], ['productId']);
            $qty = $this->values->intFrom($line, ['qty'], ['qty']);

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

    public function mapExternalReturns(array $lines): array
    {
        return array_values(array_filter(array_map(function (mixed $line): ?array {
            $id = $this->values->stringFrom($line, ['id'], ['id']);
            $description = $this->values->stringFrom($line, ['cost_description', 'costDescription'], ['costDescription']);
            $qty = $this->values->intFrom($line, ['qty'], ['qty']);

            if ($id === '' || $description === '' || $qty <= 0) {
                return null;
            }

            return [
                'source_line_id' => $id,
                'description' => $description,
                'qty' => $qty,
                'amount_rupiah' => $this->values->amountFrom($line, $qty),
            ];
        }, $lines)));
    }
}
