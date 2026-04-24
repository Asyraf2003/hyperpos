<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Core\Note\WorkItem\WorkItem;

final class NoteDetailRowPresentationSupport
{
    public function typeLabel(WorkItem $item): string
    {
        return match ($item->transactionType()) {
            WorkItem::TYPE_STORE_STOCK_SALE_ONLY => 'Produk Toko',
            WorkItem::TYPE_SERVICE_ONLY => 'Service Only',
            WorkItem::TYPE_SERVICE_WITH_STORE_STOCK_PART => 'Service + Part Toko',
            WorkItem::TYPE_SERVICE_WITH_EXTERNAL_PURCHASE => 'Service + Part External',
            default => 'Line Nota',
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
}
