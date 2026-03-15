<?php

declare(strict_types=1);

namespace App\Core\Note\WorkItem;

use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;

/**
 * @internal Hanya digunakan oleh WorkItem untuk menjaga kebersihan Core Entity.
 */
final class WorkItemGuard
{
    public static function assertValidCommon(string $id, string $noteId, int $lineNo, string $status): void
    {
        if (trim($id) === '') throw new DomainException('Work item id wajib ada.');
        if (trim($noteId) === '') throw new DomainException('Note id pada work item wajib ada.');
        if ($lineNo <= 0) throw new DomainException('Line number pada work item harus lebih besar dari nol.');

        $validStatuses = [WorkItem::STATUS_OPEN, WorkItem::STATUS_DONE, WorkItem::STATUS_CANCELED];
        if (!in_array(trim($status), $validStatuses, true)) {
            throw new DomainException('Status work item tidak valid.');
        }
    }

    public static function assertValidTransactionType(string $type): void
    {
        $validTypes = [
            WorkItem::TYPE_SERVICE_ONLY,
            WorkItem::TYPE_SERVICE_WITH_EXTERNAL_PURCHASE,
            WorkItem::TYPE_STORE_STOCK_SALE_ONLY,
            WorkItem::TYPE_SERVICE_WITH_STORE_STOCK_PART,
        ];
        if (!in_array(trim($type), $validTypes, true)) {
            throw new DomainException('Transaction type pada work item tidak valid.');
        }
    }

    public static function calculateSubtotal(
        string $type,
        ?ServiceDetail $serviceDetail,
        array $externalLines,
        array $storeLines
    ): Money {
        return match ($type) {
            WorkItem::TYPE_SERVICE_ONLY => $serviceDetail->servicePriceRupiah(),
            WorkItem::TYPE_SERVICE_WITH_EXTERNAL_PURCHASE => self::calcExt($serviceDetail, $externalLines),
            WorkItem::TYPE_STORE_STOCK_SALE_ONLY => self::calcStore($storeLines),
            WorkItem::TYPE_SERVICE_WITH_STORE_STOCK_PART => $serviceDetail->servicePriceRupiah()->add(self::calcStore($storeLines)),
            default => throw new DomainException('Tipe transaksi tidak dikenal untuk kalkulasi.')
        };
    }

    public static function validateState(
        string $type,
        ?ServiceDetail $serviceDetail,
        array $externalLines,
        array $storeLines
    ): void {
        match ($type) {
            WorkItem::TYPE_SERVICE_ONLY => self::guardServiceOnly($serviceDetail, $externalLines, $storeLines),
            WorkItem::TYPE_SERVICE_WITH_EXTERNAL_PURCHASE => self::guardExt($serviceDetail, $externalLines, $storeLines),
            WorkItem::TYPE_STORE_STOCK_SALE_ONLY => self::guardStoreOnly($serviceDetail, $externalLines, $storeLines),
            WorkItem::TYPE_SERVICE_WITH_STORE_STOCK_PART => self::guardServiceStore($serviceDetail, $externalLines, $storeLines),
            default => null
        };
    }

    private static function calcExt(ServiceDetail $sd, array $lines): Money {
        $total = $sd->servicePriceRupiah();
        foreach ($lines as $l) $total = $total->add($l->lineTotalRupiah());
        return $total;
    }

    private static function calcStore(array $lines): Money {
        $total = Money::zero();
        foreach ($lines as $l) $total = $total->add($l->lineTotalRupiah());
        return $total;
    }

    private static function guardServiceOnly(?ServiceDetail $sd, array $ext, array $store): void {
        if ($sd === null) throw new DomainException('Service detail wajib ada.');
        if ($ext !== [] || $store !== []) throw new DomainException('Lines harus kosong untuk service only.');
        if ($sd->partSource() === ServiceDetail::PART_SOURCE_STORE_STOCK) throw new DomainException('Part source tidak valid.');
    }

    private static function guardExt(?ServiceDetail $sd, array $ext, array $store): void {
        if ($sd === null || $ext === []) throw new DomainException('Detail atau External lines tidak boleh kosong.');
        if ($store !== []) throw new DomainException('Store lines harus kosong.');
    }

    private static function guardStoreOnly(?ServiceDetail $sd, array $ext, array $store): void {
        if ($sd !== null || $ext !== []) throw new DomainException('Service/External harus kosong.');
        if ($store === []) throw new DomainException('Store lines minimal satu.');
    }

    private static function guardServiceStore(?ServiceDetail $sd, array $ext, array $store): void {
        if ($sd === null || $store === []) throw new DomainException('Detail atau Store lines tidak boleh kosong.');
        if ($ext !== []) throw new DomainException('External lines harus kosong.');
    }
}
