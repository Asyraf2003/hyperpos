<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Core\Note\WorkItem\ExternalPurchaseLine;
use App\Core\Note\WorkItem\StoreStockLine;
use App\Core\Note\WorkItem\WorkItem;

final class NoteDetailRowSubtitleBuilder
{
    public function resolve(WorkItem $item): ?string
    {
        return match ($item->transactionType()) {
            WorkItem::TYPE_SERVICE_ONLY => null,
            WorkItem::TYPE_STORE_STOCK_SALE_ONLY => $this->storeStockSummary($item->storeStockLines()),
            WorkItem::TYPE_SERVICE_WITH_STORE_STOCK_PART => $this->storeStockSummary($item->storeStockLines()),
            WorkItem::TYPE_SERVICE_WITH_EXTERNAL_PURCHASE => $this->externalSummary($item->externalPurchaseLines()),
            default => null,
        };
    }

    /**
     * @param array<int, StoreStockLine> $lines
     */
    private function storeStockSummary(array $lines): ?string
    {
        if ($lines === []) {
            return null;
        }

        $parts = [];

        foreach ($lines as $line) {
            $parts[] = $line->productId() . ' x' . $line->qty();
        }

        return implode(' • ', $parts);
    }

    /**
     * @param array<int, ExternalPurchaseLine> $lines
     */
    private function externalSummary(array $lines): ?string
    {
        if ($lines === []) {
            return null;
        }

        $parts = [];

        foreach ($lines as $line) {
            $parts[] = $line->costDescription() . ' x' . $line->qty();
        }

        return implode(' • ', $parts);
    }
}
