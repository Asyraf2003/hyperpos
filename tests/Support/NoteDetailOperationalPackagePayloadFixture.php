<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Core\Note\WorkItem\WorkItem;

trait NoteDetailOperationalPackagePayloadFixture
{
    private function detailPackageRevisionLine(): array
    {
        return [
            'id' => 'rev-detail-package-1-l001',
            'work_item_root_id' => 'wi-detail-package-1',
            'line_no' => 1,
            'transaction_type' => WorkItem::TYPE_SERVICE_WITH_STORE_STOCK_PART,
            'status' => WorkItem::STATUS_OPEN,
            'service_label' => 'Service Paket Detail',
            'service_price_rupiah' => 120000,
            'subtotal_rupiah' => 250000,
            'payload' => $this->detailPackageRevisionPayload(),
        ];
    }

    private function detailPackageRevisionPayload(): array
    {
        return [
            'work_item_root_id' => 'wi-detail-package-1',
            'transaction_type' => WorkItem::TYPE_SERVICE_WITH_STORE_STOCK_PART,
            'status' => WorkItem::STATUS_OPEN,
            'external_purchase_lines' => [],
            'store_stock_lines' => [
                [
                    'id' => 'sto-detail-a',
                    'product_id' => 'product-detail-a',
                    'qty' => 2,
                    'line_total_rupiah' => 100000,
                ],
                [
                    'id' => 'sto-detail-b',
                    'product_id' => 'product-detail-b',
                    'qty' => 1,
                    'line_total_rupiah' => 30000,
                ],
            ],
            'service' => [
                'service_name' => 'Service Paket Detail',
                'service_price_rupiah' => 120000,
                'part_source' => 'store_stock',
            ],
        ];
    }
}
