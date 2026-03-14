<?php

declare(strict_types=1);

namespace App\Core\Note\WorkItem;

use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;

final class WorkItem
{
    public const STATUS_OPEN = 'open';
    public const STATUS_DONE = 'done';
    public const STATUS_CANCELED = 'canceled';

    public const TYPE_SERVICE_ONLY = 'service_only';
    public const TYPE_SERVICE_WITH_EXTERNAL_PURCHASE = 'service_with_external_purchase';
    public const TYPE_STORE_STOCK_SALE_ONLY = 'store_stock_sale_only';
    public const TYPE_SERVICE_WITH_STORE_STOCK_PART = 'service_with_store_stock_part';

    /**
     * @param list<ExternalPurchaseLine> $externalPurchaseLines
     * @param list<StoreStockLine> $storeStockLines
     */
    private function __construct(
        private string $id,
        private string $noteId,
        private int $lineNo,
        private string $transactionType,
        private string $status,
        private Money $subtotalRupiah,
        private ?ServiceDetail $serviceDetail,
        private array $externalPurchaseLines,
        private array $storeStockLines,
    ) {
    }

    public static function createServiceOnly(
        string $id,
        string $noteId,
        int $lineNo,
        ServiceDetail $serviceDetail,
        string $status = self::STATUS_OPEN,
    ): self {
        self::assertValidCommon($id, $noteId, $lineNo, $status);
        self::assertServiceOnlyServiceDetail($serviceDetail);

        return new self(
            trim($id),
            trim($noteId),
            $lineNo,
            self::TYPE_SERVICE_ONLY,
            trim($status),
            $serviceDetail->servicePriceRupiah(),
            $serviceDetail,
            [],
            [],
        );
    }

    /**
     * @param list<ExternalPurchaseLine> $externalPurchaseLines
     */
    public static function createServiceWithExternalPurchase(
        string $id,
        string $noteId,
        int $lineNo,
        ServiceDetail $serviceDetail,
        array $externalPurchaseLines,
        string $status = self::STATUS_OPEN,
    ): self {
        self::assertValidCommon($id, $noteId, $lineNo, $status);
        self::assertServiceWithExternalPurchaseDetail($serviceDetail, $externalPurchaseLines);

        return new self(
            trim($id),
            trim($noteId),
            $lineNo,
            self::TYPE_SERVICE_WITH_EXTERNAL_PURCHASE,
            trim($status),
            self::calculateSubtotalForServiceWithExternalPurchase($serviceDetail, $externalPurchaseLines),
            $serviceDetail,
            array_values($externalPurchaseLines),
            [],
        );
    }

    /**
     * @param list<StoreStockLine> $storeStockLines
     */
    public static function createStoreStockSaleOnly(
        string $id,
        string $noteId,
        int $lineNo,
        array $storeStockLines,
        string $status = self::STATUS_OPEN,
    ): self {
        self::assertValidCommon($id, $noteId, $lineNo, $status);
        self::assertStoreStockSaleOnlyDetail($storeStockLines);

        return new self(
            trim($id),
            trim($noteId),
            $lineNo,
            self::TYPE_STORE_STOCK_SALE_ONLY,
            trim($status),
            self::calculateSubtotalForStoreStockLines($storeStockLines),
            null,
            [],
            array_values($storeStockLines),
        );
    }

    /**
     * @param list<StoreStockLine> $storeStockLines
     */
    public static function createServiceWithStoreStockPart(
        string $id,
        string $noteId,
        int $lineNo,
        ServiceDetail $serviceDetail,
        array $storeStockLines,
        string $status = self::STATUS_OPEN,
    ): self {
        self::assertValidCommon($id, $noteId, $lineNo, $status);
        self::assertServiceWithStoreStockPartDetail($serviceDetail, $storeStockLines);

        return new self(
            trim($id),
            trim($noteId),
            $lineNo,
            self::TYPE_SERVICE_WITH_STORE_STOCK_PART,
            trim($status),
            self::calculateSubtotalForServiceWithStoreStockPart($serviceDetail, $storeStockLines),
            $serviceDetail,
            [],
            array_values($storeStockLines),
        );
    }

    /**
     * @param list<ExternalPurchaseLine> $externalPurchaseLines
     * @param list<StoreStockLine> $storeStockLines
     */
    public static function rehydrate(
        string $id,
        string $noteId,
        int $lineNo,
        string $transactionType,
        string $status,
        Money $subtotalRupiah,
        ?ServiceDetail $serviceDetail,
        array $externalPurchaseLines = [],
        array $storeStockLines = [],
    ): self {
        self::assertValidCommon($id, $noteId, $lineNo, $status);
        self::assertValidTransactionType($transactionType);
        self::assertValidExternalPurchaseLines($externalPurchaseLines);
        self::assertValidStoreStockLines($storeStockLines);

        $normalizedTransactionType = trim($transactionType);

        if ($normalizedTransactionType === self::TYPE_SERVICE_ONLY) {
            if ($serviceDetail === null) {
                throw new DomainException('Service detail wajib ada untuk work item service only.');
            }

            self::assertServiceOnlyServiceDetail($serviceDetail);

            if ($externalPurchaseLines !== []) {
                throw new DomainException('External purchase lines harus kosong untuk work item service only.');
            }

            if ($storeStockLines !== []) {
                throw new DomainException('Store stock lines harus kosong untuk work item service only.');
            }

            if ($subtotalRupiah->equals($serviceDetail->servicePriceRupiah()) === false) {
                throw new DomainException('Subtotal work item service only tidak konsisten.');
            }
        }

        if ($normalizedTransactionType === self::TYPE_SERVICE_WITH_EXTERNAL_PURCHASE) {
            if ($serviceDetail === null) {
                throw new DomainException('Service detail wajib ada untuk work item service with external purchase.');
            }

            self::assertServiceWithExternalPurchaseDetail($serviceDetail, $externalPurchaseLines);

            if ($storeStockLines !== []) {
                throw new DomainException('Store stock lines harus kosong untuk work item service with external purchase.');
            }

            $calculatedSubtotal = self::calculateSubtotalForServiceWithExternalPurchase(
                $serviceDetail,
                $externalPurchaseLines,
            );

            if ($subtotalRupiah->equals($calculatedSubtotal) === false) {
                throw new DomainException('Subtotal work item service with external purchase tidak konsisten.');
            }
        }

        if ($normalizedTransactionType === self::TYPE_STORE_STOCK_SALE_ONLY) {
            if ($serviceDetail !== null) {
                throw new DomainException('Service detail harus kosong untuk work item store stock sale only.');
            }

            if ($externalPurchaseLines !== []) {
                throw new DomainException('External purchase lines harus kosong untuk work item store stock sale only.');
            }

            self::assertStoreStockSaleOnlyDetail($storeStockLines);

            $calculatedSubtotal = self::calculateSubtotalForStoreStockLines($storeStockLines);

            if ($subtotalRupiah->equals($calculatedSubtotal) === false) {
                throw new DomainException('Subtotal work item store stock sale only tidak konsisten.');
            }
        }

        if ($normalizedTransactionType === self::TYPE_SERVICE_WITH_STORE_STOCK_PART) {
            if ($serviceDetail === null) {
                throw new DomainException('Service detail wajib ada untuk work item service with store stock part.');
            }

            if ($externalPurchaseLines !== []) {
                throw new DomainException('External purchase lines harus kosong untuk work item service with store stock part.');
            }

            self::assertServiceWithStoreStockPartDetail($serviceDetail, $storeStockLines);

            $calculatedSubtotal = self::calculateSubtotalForServiceWithStoreStockPart(
                $serviceDetail,
                $storeStockLines,
            );

            if ($subtotalRupiah->equals($calculatedSubtotal) === false) {
                throw new DomainException('Subtotal work item service with store stock part tidak konsisten.');
            }
        }

        return new self(
            trim($id),
            trim($noteId),
            $lineNo,
            $normalizedTransactionType,
            trim($status),
            $subtotalRupiah,
            $serviceDetail,
            array_values($externalPurchaseLines),
            array_values($storeStockLines),
        );
    }

    public function markDone(): void
    {
        $this->status = self::STATUS_DONE;
    }

    public function cancel(): void
    {
        $this->status = self::STATUS_CANCELED;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function noteId(): string
    {
        return $this->noteId;
    }

    public function lineNo(): int
    {
        return $this->lineNo;
    }

    public function transactionType(): string
    {
        return $this->transactionType;
    }

    public function status(): string
    {
        return $this->status;
    }

    public function subtotalRupiah(): Money
    {
        return $this->subtotalRupiah;
    }

    public function serviceDetail(): ?ServiceDetail
    {
        return $this->serviceDetail;
    }

    /**
     * @return list<ExternalPurchaseLine>
     */
    public function externalPurchaseLines(): array
    {
        return $this->externalPurchaseLines;
    }

    /**
     * @return list<StoreStockLine>
     */
    public function storeStockLines(): array
    {
        return $this->storeStockLines;
    }

    private static function assertValidCommon(
        string $id,
        string $noteId,
        int $lineNo,
        string $status,
    ): void {
        if (trim($id) === '') {
            throw new DomainException('Work item id wajib ada.');
        }

        if (trim($noteId) === '') {
            throw new DomainException('Note id pada work item wajib ada.');
        }

        if ($lineNo <= 0) {
            throw new DomainException('Line number pada work item harus lebih besar dari nol.');
        }

        $normalizedStatus = trim($status);

        if (in_array(
            $normalizedStatus,
            [
                self::STATUS_OPEN,
                self::STATUS_DONE,
                self::STATUS_CANCELED,
            ],
            true
        ) === false) {
            throw new DomainException('Status work item tidak valid.');
        }
    }

    private static function assertValidTransactionType(string $transactionType): void
    {
        $normalizedTransactionType = trim($transactionType);

        if (in_array(
            $normalizedTransactionType,
            [
                self::TYPE_SERVICE_ONLY,
                self::TYPE_SERVICE_WITH_EXTERNAL_PURCHASE,
                self::TYPE_STORE_STOCK_SALE_ONLY,
                self::TYPE_SERVICE_WITH_STORE_STOCK_PART,
            ],
            true
        ) === false) {
            throw new DomainException('Transaction type pada work item tidak valid.');
        }
    }

    private static function assertServiceOnlyServiceDetail(ServiceDetail $serviceDetail): void
    {
        if ($serviceDetail->partSource() !== ServiceDetail::PART_SOURCE_NONE
            && $serviceDetail->partSource() !== ServiceDetail::PART_SOURCE_CUSTOMER_OWNED) {
            throw new DomainException('Part source untuk service only tidak valid.');
        }
    }

    /**
     * @param list<ExternalPurchaseLine> $externalPurchaseLines
     */
    private static function assertServiceWithExternalPurchaseDetail(
        ServiceDetail $serviceDetail,
        array $externalPurchaseLines,
    ): void {
        if ($serviceDetail->partSource() !== ServiceDetail::PART_SOURCE_NONE) {
            throw new DomainException('Part source untuk service with external purchase harus none.');
        }

        if ($externalPurchaseLines === []) {
            throw new DomainException('External purchase lines minimal harus memiliki satu line.');
        }

        self::assertValidExternalPurchaseLines($externalPurchaseLines);
    }

    /**
     * @param list<StoreStockLine> $storeStockLines
     */
    private static function assertStoreStockSaleOnlyDetail(array $storeStockLines): void
    {
        if ($storeStockLines === []) {
            throw new DomainException('Store stock lines minimal harus memiliki satu line.');
        }

        self::assertValidStoreStockLines($storeStockLines);
    }

    /**
     * @param list<StoreStockLine> $storeStockLines
     */
    private static function assertServiceWithStoreStockPartDetail(
        ServiceDetail $serviceDetail,
        array $storeStockLines,
    ): void {
        if ($serviceDetail->partSource() !== ServiceDetail::PART_SOURCE_NONE) {
            throw new DomainException('Part source untuk service with store stock part harus none.');
        }

        if ($storeStockLines === []) {
            throw new DomainException('Store stock lines minimal harus memiliki satu line.');
        }

        self::assertValidStoreStockLines($storeStockLines);
    }

    /**
     * @param list<ExternalPurchaseLine> $externalPurchaseLines
     */
    private static function assertValidExternalPurchaseLines(array $externalPurchaseLines): void
    {
        foreach ($externalPurchaseLines as $line) {
            if ($line instanceof ExternalPurchaseLine === false) {
                throw new DomainException('External purchase line pada work item tidak valid.');
            }
        }
    }

    /**
     * @param list<StoreStockLine> $storeStockLines
     */
    private static function assertValidStoreStockLines(array $storeStockLines): void
    {
        foreach ($storeStockLines as $line) {
            if ($line instanceof StoreStockLine === false) {
                throw new DomainException('Store stock line pada work item tidak valid.');
            }
        }
    }

    /**
     * @param list<ExternalPurchaseLine> $externalPurchaseLines
     */
    private static function calculateSubtotalForServiceWithExternalPurchase(
        ServiceDetail $serviceDetail,
        array $externalPurchaseLines,
    ): Money {
        $subtotal = $serviceDetail->servicePriceRupiah();

        foreach ($externalPurchaseLines as $line) {
            $subtotal = $subtotal->add($line->lineTotalRupiah());
        }

        return $subtotal;
    }

    /**
     * @param list<StoreStockLine> $storeStockLines
     */
    private static function calculateSubtotalForStoreStockLines(array $storeStockLines): Money
    {
        $subtotal = Money::zero();

        foreach ($storeStockLines as $line) {
            $subtotal = $subtotal->add($line->lineTotalRupiah());
        }

        return $subtotal;
    }

    /**
     * @param list<StoreStockLine> $storeStockLines
     */
    private static function calculateSubtotalForServiceWithStoreStockPart(
        ServiceDetail $serviceDetail,
        array $storeStockLines,
    ): Money {
        return $serviceDetail->servicePriceRupiah()
            ->add(self::calculateSubtotalForStoreStockLines($storeStockLines));
    }
}
