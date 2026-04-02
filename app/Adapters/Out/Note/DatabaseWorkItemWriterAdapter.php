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

    public function updateStatus(WorkItem $workItem): void
    {
        DB::table('work_items')->where('id', $workItem->id())->update([
            'status' => $workItem->status(),
        ]);
    }

    public function updateServiceOnly(WorkItem $workItem): void
    {
        $serviceDetail = $this->assertServiceOnlyUpdatable($workItem);
        $this->updateSubtotalAndServiceDetail($workItem, $serviceDetail);
    }

    public function updateServiceWithStoreStockPartServiceFeeOnly(WorkItem $workItem): void
    {
        $serviceDetail = $this->assertServiceWithStoreStockPartFeeOnlyUpdatable($workItem);
        $this->updateSubtotalAndServiceDetail($workItem, $serviceDetail);
    }

    private function updateSubtotalAndServiceDetail(WorkItem $workItem, ServiceDetail $serviceDetail): void
    {
        DB::table('work_items')->where('id', $workItem->id())->update([
            'subtotal_rupiah' => $workItem->subtotalRupiah()->amount(),
        ]);

        DB::table('work_item_service_details')->where('work_item_id', $workItem->id())->update([
            'service_name' => $serviceDetail->serviceName(),
            'service_price_rupiah' => $serviceDetail->servicePriceRupiah()->amount(),
            'part_source' => $serviceDetail->partSource(),
        ]);
    }

    private function assertServiceOnlyUpdatable(WorkItem $workItem): ServiceDetail
    {
        if ($workItem->transactionType() !== WorkItem::TYPE_SERVICE_ONLY) {
            throw new DomainException('Update service only hanya boleh untuk work item service_only.');
        }

        $serviceDetail = $workItem->serviceDetail();

        if (!$serviceDetail instanceof ServiceDetail) {
            throw new DomainException('Service detail wajib ada untuk update service only.');
        }

        if ($workItem->externalPurchaseLines() !== [] || $workItem->storeStockLines() !== []) {
            throw new DomainException('Work item service only tidak boleh memiliki external/store stock lines.');
        }

        return $serviceDetail;
    }

    private function assertServiceWithStoreStockPartFeeOnlyUpdatable(WorkItem $workItem): ServiceDetail
    {
        if ($workItem->transactionType() !== WorkItem::TYPE_SERVICE_WITH_STORE_STOCK_PART) {
            throw new DomainException('Update service fee only hanya boleh untuk work item service_with_store_stock_part.');
        }

        $serviceDetail = $workItem->serviceDetail();

        if (!$serviceDetail instanceof ServiceDetail) {
            throw new DomainException('Service detail wajib ada untuk update service_with_store_stock_part.');
        }

        if ($workItem->storeStockLines() === []) {
            throw new DomainException('Work item service_with_store_stock_part wajib memiliki store stock lines.');
        }

        if ($workItem->externalPurchaseLines() !== []) {
            throw new DomainException('Work item service_with_store_stock_part tidak boleh memiliki external purchase lines.');
        }

        return $serviceDetail;
    }
}
