<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Core\Note\WorkItem\ExternalPurchaseLine;
use App\Core\Note\WorkItem\StoreStockLine;
use App\Core\Note\WorkItem\WorkItem;

final class NoteDetailRowPresentationSupport
{
    public function typeLabel(WorkItem $item): string
    {
        return match ($item->transactionType()) {
            WorkItem::TYPE_STORE_STOCK_SALE_ONLY => 'Produk Toko',
            WorkItem::TYPE_SERVICE_ONLY => 'Service',
            WorkItem::TYPE_SERVICE_WITH_STORE_STOCK_PART => 'Service + Part Toko',
            WorkItem::TYPE_SERVICE_WITH_EXTERNAL_PURCHASE => 'Service + Part External',
            default => 'Line Nota',
        };
    }

    public function lineLabel(WorkItem $item): string
    {
        $serviceName = trim((string) ($item->serviceDetail()?->serviceName() ?? ''));
        $storeLines = $item->storeStockLines();
        $externalLines = $item->externalPurchaseLines();

        return match ($item->transactionType()) {
            WorkItem::TYPE_SERVICE_ONLY => $serviceName !== '' ? $serviceName : 'Service',
            WorkItem::TYPE_STORE_STOCK_SALE_ONLY => $this->storeStockPrimaryLabel($storeLines) ?? 'Produk',
            WorkItem::TYPE_SERVICE_WITH_STORE_STOCK_PART => $serviceName !== ''
                ? $serviceName
                : ($this->storeStockPrimaryLabel($storeLines) ?? 'Service + Part Toko'),
            WorkItem::TYPE_SERVICE_WITH_EXTERNAL_PURCHASE => $serviceName !== ''
                ? $serviceName
                : ($this->externalPrimaryLabel($externalLines) ?? 'Service + Part External'),
            default => $serviceName !== '' ? $serviceName : 'Line Nota',
        };
    }

    public function lineSubtitle(WorkItem $item): ?string
    {
        return match ($item->transactionType()) {
            WorkItem::TYPE_SERVICE_ONLY => null,
            WorkItem::TYPE_STORE_STOCK_SALE_ONLY => $this->storeStockSummary($item->storeStockLines()),
            WorkItem::TYPE_SERVICE_WITH_STORE_STOCK_PART => $this->storeStockSummary($item->storeStockLines()),
            WorkItem::TYPE_SERVICE_WITH_EXTERNAL_PURCHASE => $this->externalSummary($item->externalPurchaseLines()),
            default => null,
        };
    }

    public function refundPreviewLabel(int $storeStockCount, int $externalPurchaseCount): string
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

    /**
     * @param array<int, StoreStockLine> $lines
     */
    private function storeStockPrimaryLabel(array $lines): ?string
    {
        if ($lines === []) {
            return null;
        }

        $first = $lines[0];
        $label = 'Produk ' . $first->productId();
        $remaining = count($lines) - 1;

        if ($remaining > 0) {
            return $label . ' +' . $remaining . ' item';
        }

        return $label;
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
    private function externalPrimaryLabel(array $lines): ?string
    {
        if ($lines === []) {
            return null;
        }

        $first = trim($lines[0]->costDescription());
        if ($first === '') {
            return null;
        }

        $remaining = count($lines) - 1;

        if ($remaining > 0) {
            return $first . ' +' . $remaining . ' item';
        }

        return $first;
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
