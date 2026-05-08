<?php

declare(strict_types=1);

namespace App\Application\Note\Services\CurrentRevision;

final class CurrentRevisionPayloadLineExtractor
{
    /**
     * @param array<string, mixed> $payload
     * @return list<array<string, mixed>>
     */
    public function storeStockLines(array $payload): array
    {
        return $this->arrayLines($payload, 'store_stock_lines');
    }

    /**
     * @param array<string, mixed> $payload
     * @return list<array<string, mixed>>
     */
    public function externalPurchaseLines(array $payload): array
    {
        return $this->arrayLines($payload, 'external_purchase_lines');
    }

    /**
     * @param array<string, mixed> $payload
     * @return list<array<string, mixed>>
     */
    private function arrayLines(array $payload, string $key): array
    {
        $lines = $payload[$key] ?? [];

        return is_array($lines) ? array_values(array_filter($lines, 'is_array')) : [];
    }
}
