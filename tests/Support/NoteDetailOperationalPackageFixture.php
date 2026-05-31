<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Core\Note\WorkItem\WorkItem;
use Illuminate\Support\Facades\DB;

trait NoteDetailOperationalPackageFixture
{
    private function seedVisibleStoreStockPackageDetailFixture(string $today): void
    {
        $this->seedDetailPackageProducts();
        $this->seedNoteBase('note-detail-package-1', 'Pelanggan Detail', $today, 250000);

        DB::table('notes')->where('id', 'note-detail-package-1')->update([
            'operational_note' => 'Keterangan operasional detail package',
        ]);

        $this->seedWorkItemBase(
            'wi-detail-package-1',
            'note-detail-package-1',
            1,
            WorkItem::TYPE_SERVICE_WITH_STORE_STOCK_PART,
            WorkItem::STATUS_OPEN,
            250000,
        );

        $this->seedServiceDetailBase(
            'wi-detail-package-1',
            'Service Paket Detail',
            120000,
            'store_stock',
        );

        $this->seedStoreStockLineBase('sto-detail-a', 'wi-detail-package-1', 'product-detail-a', 2, 100000);
        $this->seedStoreStockLineBase('sto-detail-b', 'wi-detail-package-1', 'product-detail-b', 1, 30000);
        $this->seedDetailPackageCurrentRevision($today);
    }

    private function seedDetailPackageProducts(): void
    {
        $this->seedNotePaymentProduct(
            'product-detail-a',
            'FILTER-DETAIL-A',
            'Filter Oli Detail',
            'Yamaha',
            100,
            50000,
        );

        $this->seedNotePaymentProduct(
            'product-detail-b',
            'BUSI-DETAIL-B',
            'Busi Iridium Detail',
            'NGK',
            1,
            30000,
        );
    }

    private function seedDetailPackageCurrentRevision(string $today): void
    {
        $this->seedCurrentRevision(
            'note-detail-package-1',
            'rev-detail-package-1',
            'Pelanggan Detail',
            null,
            $today,
            250000,
            [$this->detailPackageRevisionLine()],
        );
    }

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
                ['id' => 'sto-detail-a', 'product_id' => 'product-detail-a', 'qty' => 2, 'line_total_rupiah' => 100000],
                ['id' => 'sto-detail-b', 'product_id' => 'product-detail-b', 'qty' => 1, 'line_total_rupiah' => 30000],
            ],
            'service' => [
                'service_name' => 'Service Paket Detail',
                'service_price_rupiah' => 120000,
                'part_source' => 'store_stock',
            ],
        ];
    }
}
