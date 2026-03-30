<?php

declare(strict_types=1);

namespace App\Adapters\Out\Note;

use App\Core\Note\WorkItem\ServiceDetail;
use App\Core\Note\WorkItem\WorkItem;
use App\Core\Shared\Exceptions\DomainException;
use App\Ports\Out\Note\WorkItemWriterPort;
use Illuminate\Support\Facades\DB;

final class DatabaseWorkItemWriterAdapter implements WorkItemWriterPort
{
    use WorkItemLineInsertsTrait;

    public function create(WorkItem $workItem): void
    {
        DB::table('work_items')->insert([
            'id' => $workItem->id(),
            'note_id' => $workItem->noteId(),
            'line_no' => $workItem->lineNo(),
            'transaction_type' => $workItem->transactionType(),
            'status' => $workItem->status(),
            'subtotal_rupiah' => $workItem->subtotalRupiah()->amount(),
        ]);

        $serviceDetail = $workItem->serviceDetail();

        if ($serviceDetail !== null) {
            DB::table('work_item_service_details')->insert([
                'work_item_id' => $workItem->id(),
                'service_name' => $serviceDetail->serviceName(),
                'service_price_rupiah' => $serviceDetail->servicePriceRupiah()->amount(),
                'part_source' => $serviceDetail->partSource(),
            ]);
        }

        $this->insertExternalPurchaseLines($workItem);
        $this->insertStoreStockLines($workItem);
    }

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

        DB::table('work_item_service_details')
            ->whereIn('work_item_id', $workItemIds)
            ->delete();

        DB::table('work_item_external_purchase_lines')
            ->whereIn('work_item_id', $workItemIds)
            ->delete();

        DB::table('work_item_store_stock_lines')
            ->whereIn('work_item_id', $workItemIds)
            ->delete();

        DB::table('work_items')
            ->whereIn('id', $workItemIds)
            ->delete();
    }

    public function updateStatus(WorkItem $workItem): void
    {
        DB::table('work_items')
            ->where('id', $workItem->id())
            ->update([
                'status' => $workItem->status(),
            ]);
    }

    public function updateServiceOnly(WorkItem $workItem): void
    {
        if ($workItem->transactionType() !== WorkItem::TYPE_SERVICE_ONLY) {
            throw new DomainException('Update service only hanya boleh untuk work item service_only.');
        }

        $serviceDetail = $workItem->serviceDetail();

        if (! $serviceDetail instanceof ServiceDetail) {
            throw new DomainException('Service detail wajib ada untuk update service only.');
        }

        if ($workItem->externalPurchaseLines() !== [] || $workItem->storeStockLines() !== []) {
            throw new DomainException('Work item service only tidak boleh memiliki external/store stock lines.');
        }

        DB::table('work_items')
            ->where('id', $workItem->id())
            ->update([
                'subtotal_rupiah' => $workItem->subtotalRupiah()->amount(),
            ]);

        DB::table('work_item_service_details')
            ->where('work_item_id', $workItem->id())
            ->update([
                'service_name' => $serviceDetail->serviceName(),
                'service_price_rupiah' => $serviceDetail->servicePriceRupiah()->amount(),
                'part_source' => $serviceDetail->partSource(),
            ]);
    }
}
