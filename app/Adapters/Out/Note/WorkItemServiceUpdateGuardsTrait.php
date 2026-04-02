<?php

declare(strict_types=1);

namespace App\Adapters\Out\Note;

use App\Core\Note\WorkItem\ServiceDetail;
use App\Core\Note\WorkItem\WorkItem;
use App\Core\Shared\Exceptions\DomainException;
use Illuminate\Support\Facades\DB;

trait WorkItemServiceUpdateGuardsTrait
{
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

    private function assertServiceWithExternalPurchaseFeeOnlyUpdatable(WorkItem $workItem): ServiceDetail
    {
        if ($workItem->transactionType() !== WorkItem::TYPE_SERVICE_WITH_EXTERNAL_PURCHASE) {
            throw new DomainException('Update service fee only hanya boleh untuk work item service_with_external_purchase.');
        }

        $serviceDetail = $workItem->serviceDetail();

        if (!$serviceDetail instanceof ServiceDetail) {
            throw new DomainException('Service detail wajib ada untuk update service_with_external_purchase.');
        }

        if ($workItem->externalPurchaseLines() === []) {
            throw new DomainException('Work item service_with_external_purchase wajib memiliki external purchase lines.');
        }

        if ($workItem->storeStockLines() !== []) {
            throw new DomainException('Work item service_with_external_purchase tidak boleh memiliki store stock lines.');
        }

        return $serviceDetail;
    }
}
