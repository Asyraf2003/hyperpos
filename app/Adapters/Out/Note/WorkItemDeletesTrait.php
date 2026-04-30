<?php

declare(strict_types=1);

namespace App\Adapters\Out\Note;

use App\Core\Shared\Exceptions\DomainException;
use Illuminate\Support\Facades\DB;

trait WorkItemDeletesTrait
{
    public function deleteByNoteId(string $noteId): void
    {
        $normalized = trim($noteId);

        if ($normalized === '') {
            throw new DomainException('Note id pada penghapusan work item wajib ada.');
        }

        $workItemIds = DB::table('work_items')
            ->where('note_id', $normalized)
            ->pluck('id')
            ->map(static fn ($id): string => (string) $id)
            ->all();

        if ($workItemIds === []) {
            return;
        }

        $protectedWorkItemIds = DB::table('refund_component_allocations')
            ->whereIn('work_item_id', $workItemIds)
            ->pluck('work_item_id')
            ->map(static fn ($id): string => (string) $id)
            ->all();

        $protected = array_flip($protectedWorkItemIds);
        $deletableWorkItemIds = array_values(array_filter(
            $workItemIds,
            static fn (string $id): bool => ! isset($protected[$id])
        ));

        if ($deletableWorkItemIds === []) {
            return;
        }

        DB::table('work_item_service_details')
            ->whereIn('work_item_id', $deletableWorkItemIds)
            ->delete();

        DB::table('work_item_external_purchase_lines')
            ->whereIn('work_item_id', $deletableWorkItemIds)
            ->delete();

        DB::table('work_item_store_stock_lines')
            ->whereIn('work_item_id', $deletableWorkItemIds)
            ->delete();

        DB::table('work_items')
            ->whereIn('id', $deletableWorkItemIds)
            ->delete();
    }
}
