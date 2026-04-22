<?php

declare(strict_types=1);

namespace App\Adapters\Out\Note;

use App\Ports\Out\Note\WorkItemStoreStockLineReaderPort;
use Illuminate\Support\Facades\DB;

final class DatabaseWorkItemStoreStockLineReaderAdapter implements WorkItemStoreStockLineReaderPort
{
    public function listIdsByWorkItemId(string $workItemId): array
    {
        $id = trim($workItemId);
        if ($id === '') {
            return [];
        }

        return DB::table('work_item_store_stock_lines')
            ->where('work_item_id', $id)
            ->orderBy('id')
            ->pluck('id')
            ->filter(fn ($value): bool => is_string($value) && trim($value) !== '')
            ->map(fn (string $value): string => trim($value))
            ->values()
            ->all();
    }
}
