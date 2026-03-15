<?php

declare(strict_types=1);

namespace App\Application\Note\UseCases;

use App\Application\Inventory\Services\IssueInventoryOperation;
use App\Application\Shared\DTO\Result;
use App\Core\Note\WorkItem\ExternalPurchaseLine;
use App\Core\Note\WorkItem\ServiceDetail;
use App\Core\Note\WorkItem\StoreStockLine;
use App\Core\Note\WorkItem\WorkItem;
use App\Core\ProductCatalog\Product\Product;
use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;
use App\Ports\Out\Note\NoteReaderPort;
use App\Ports\Out\Note\NoteWriterPort;
use App\Ports\Out\Note\WorkItemWriterPort;
use App\Ports\Out\Payment\PaymentAllocationReaderPort;
use App\Ports\Out\ProductCatalog\ProductReaderPort;
use App\Ports\Out\TransactionManagerPort;
use App\Ports\Out\UuidPort;
use Throwable;

final class AddWorkItemHandler
{
    public function __construct(
        private readonly NoteReaderPort $notes,
        private readonly NoteWriterPort $noteWriter,
        private readonly WorkItemWriterPort $workItems,
        private readonly PaymentAllocationReaderPort $paymentAllocations,
        private readonly ProductReaderPort $products,
        private readonly IssueInventoryOperation $issueInventory,
        private readonly TransactionManagerPort $transactions,
        private readonly UuidPort $uuid,
    ) {
    }

    /**
     * @param array<string, mixed> $serviceDetailPayload
     * @param array<int, array<string, mixed>> $externalPurchaseLinesPayload
     * @param array<int, array<string, mixed>> $storeStockLinesPayload
     */
    public function handle(
        string $noteId,
        int $lineNo,
        string $transactionType,
        array $serviceDetailPayload,
        array $externalPurchaseLinesPayload = [],
        array $storeStockLinesPayload = [],
    ): Result {
        try {
            $normalizedNoteId = $this->normalizeRequired($noteId, 'Note id pada work item wajib ada.');
            $normalizedTransactionType = $this->normalizeRequired(
                $transactionType,
                'Transaction type pada work item wajib ada.'
            );
        } catch (DomainException $e) {
            return $this->failureFromDomainException($e);
        }

        $transactionStarted = false;

        try {
            $this->transactions->begin();
            $transactionStarted = true;

            $note = $this->notes->getById($normalizedNoteId);

            if ($note === null) {
                throw new DomainException('Note tidak ditemukan.');
            }

            $this->assertNoteAllowsNewWorkItem(
                $note->id(),
                $note->totalRupiah(),
            );

            $workItem = $this->buildWorkItem(
                $note->id(),
                $lineNo,
                $normalizedTransactionType,
                $serviceDetailPayload,
                $externalPurchaseLinesPayload,
                $storeStockLinesPayload,
            );

            $note->addWorkItem($workItem);

            $this->workItems->create($workItem);

            foreach ($workItem->storeStockLines() as $storeStockLine) {
                $this->issueInventory->execute(
                    $storeStockLine->productId(),
                    $storeStockLine->qty(),
                    $note->transactionDate(),
                    'work_item_store_stock_line',
                    $storeStockLine->id(),
                );
            }

            $this->noteWriter->updateTotal($note);

            $this->transactions->commit();

            return Result::success(
                [
                    'note' => [
                        'id' => $note->id(),
                        'customer_name' => $note->customerName(),
                        'transaction_date' => $note->transactionDate()->format('Y-m-d'),
                        'total_rupiah' => $note->totalRupiah()->amount(),
                    ],
                    'work_item' => [
                        'id' => $workItem->id(),
                        'note_id' => $workItem->noteId(),
                        'line_no' => $workItem->lineNo(),
                        'transaction_type' => $workItem->transactionType(),
                        'status' => $workItem->status(),
                        'subtotal_rupiah' => $workItem->subtotalRupiah()->amount(),
                    ],
                    'service_detail' => $workItem->serviceDetail() === null
                        ? null
                        : [
                            'service_name' => $workItem->serviceDetail()?->serviceName(),
                            'service_price_rupiah' => $workItem->serviceDetail()?->servicePriceRupiah()->amount(),
                            'part_source' => $workItem->serviceDetail()?->partSource(),
                        ],
                    'external_purchase_lines' => array_map(
                        static fn (ExternalPurchaseLine $line): array => [
                            'id' => $line->id(),
                            'cost_description' => $line->costDescription(),
                            'unit_cost_rupiah' => $line->unitCostRupiah()->amount(),
                            'qty' => $line->qty(),
                            'line_total_rupiah' => $line->lineTotalRupiah()->amount(),
                        ],
                        $workItem->externalPurchaseLines(),
                    ),
                    'store_stock_lines' => array_map(
                        static fn (StoreStockLine $line): array => [
                            'id' => $line->id(),
                            'product_id' => $line->productId(),
                            'qty' => $line->qty(),
                            'line_total_rupiah' => $line->lineTotalRupiah()->amount(),
                        ],
                        $workItem->storeStockLines(),
                    ),
                ],
                'Work item berhasil ditambahkan ke note.'
            );
        } catch (DomainException $e) {
            if ($transactionStarted) {
                $this->transactions->rollBack();
            }

            return $this->failureFromDomainException($e);
        } catch (Throwable $e) {
            if ($transactionStarted) {
                $this->transactions->rollBack();
            }

            throw $e;
        }
    }

    private function assertNoteAllowsNewWorkItem(
        string $noteId,
        Money $noteTotalRupiah,
    ): void {
        if ($noteTotalRupiah->greaterThan(Money::zero()) === false) {
            return;
        }

        $totalAllocatedByNoteRupiah = $this->paymentAllocations
            ->getTotalAllocatedAmountByNoteId($noteId);

        $totalAllocatedByNoteRupiah->ensureNotNegative('Total alokasi pada note tidak boleh negatif.');

        if ($totalAllocatedByNoteRupiah->greaterThanOrEqual($noteTotalRupiah)) {
            throw new DomainException('Item baru tidak boleh ditambahkan ke note yang sudah lunas.');
        }
    }

    /**
     * @param array<string, mixed> $serviceDetailPayload
     * @param array<int, array<string, mixed>> $externalPurchaseLinesPayload
     * @param array<int, array<string, mixed>> $storeStockLinesPayload
     */
    private function buildWorkItem(
        string $noteId,
        int $lineNo,
        string $transactionType,
        array $serviceDetailPayload,
        array $externalPurchaseLinesPayload,
        array $storeStockLinesPayload,
    ): WorkItem {
        if ($transactionType === WorkItem::TYPE_SERVICE_ONLY) {
            return WorkItem::createServiceOnly(
                $this->uuid->generate(),
                $noteId,
                $lineNo,
                $this->buildServiceDetail($serviceDetailPayload),
            );
        }

        if ($transactionType === WorkItem::TYPE_SERVICE_WITH_EXTERNAL_PURCHASE) {
            return WorkItem::createServiceWithExternalPurchase(
                $this->uuid->generate(),
                $noteId,
                $lineNo,
                $this->buildServiceDetail($serviceDetailPayload),
                $this->buildExternalPurchaseLines($externalPurchaseLinesPayload),
            );
        }

        if ($transactionType === WorkItem::TYPE_STORE_STOCK_SALE_ONLY) {
            return WorkItem::createStoreStockSaleOnly(
                $this->uuid->generate(),
                $noteId,
                $lineNo,
                $this->buildStoreStockLines($storeStockLinesPayload),
            );
        }

        if ($transactionType === WorkItem::TYPE_SERVICE_WITH_STORE_STOCK_PART) {
            return WorkItem::createServiceWithStoreStockPart(
                $this->uuid->generate(),
                $noteId,
                $lineNo,
                $this->buildServiceDetail($serviceDetailPayload),
                $this->buildStoreStockLines($storeStockLinesPayload),
            );
        }

        throw new DomainException('Transaction type work item belum didukung pada slice ini.');
    }

    /**
     * @param array<string, mixed> $serviceDetailPayload
     */
    private function buildServiceDetail(array $serviceDetailPayload): ServiceDetail
    {
        if (array_key_exists('service_name', $serviceDetailPayload) === false) {
            throw new DomainException('Service name pada work item wajib ada.');
        }

        if (array_key_exists('service_price_rupiah', $serviceDetailPayload) === false) {
            throw new DomainException('Service price rupiah pada work item wajib ada.');
        }

        $partSource = array_key_exists('part_source', $serviceDetailPayload)
            ? (string) $serviceDetailPayload['part_source']
            : ServiceDetail::PART_SOURCE_NONE;

        return ServiceDetail::create(
            trim((string) $serviceDetailPayload['service_name']),
            Money::fromInt((int) $serviceDetailPayload['service_price_rupiah']),
            $partSource,
        );
    }

    /**
     * @param array<int, array<string, mixed>> $externalPurchaseLinesPayload
     * @return list<ExternalPurchaseLine>
     */
    private function buildExternalPurchaseLines(array $externalPurchaseLinesPayload): array
    {
        $externalPurchaseLines = [];

        foreach ($externalPurchaseLinesPayload as $linePayload) {
            if (array_key_exists('cost_description', $linePayload) === false) {
                throw new DomainException('Cost description pada external purchase line wajib ada.');
            }

            if (array_key_exists('unit_cost_rupiah', $linePayload) === false) {
                throw new DomainException('Unit cost rupiah pada external purchase line wajib ada.');
            }

            if (array_key_exists('qty', $linePayload) === false) {
                throw new DomainException('Qty pada external purchase line wajib ada.');
            }

            $externalPurchaseLines[] = ExternalPurchaseLine::create(
                $this->uuid->generate(),
                trim((string) $linePayload['cost_description']),
                Money::fromInt((int) $linePayload['unit_cost_rupiah']),
                (int) $linePayload['qty'],
            );
        }

        return $externalPurchaseLines;
    }

    /**
     * @param array<int, array<string, mixed>> $storeStockLinesPayload
     * @return list<StoreStockLine>
     */
    private function buildStoreStockLines(array $storeStockLinesPayload): array
    {
        $storeStockLines = [];

        foreach ($storeStockLinesPayload as $linePayload) {
            if (array_key_exists('product_id', $linePayload) === false) {
                throw new DomainException('Product id pada store stock line wajib ada.');
            }

            if (array_key_exists('qty', $linePayload) === false) {
                throw new DomainException('Qty pada store stock line wajib ada.');
            }

            if (array_key_exists('line_total_rupiah', $linePayload) === false) {
                throw new DomainException('Line total rupiah pada store stock line wajib ada.');
            }

            $product = $this->getExistingProduct((string) $linePayload['product_id']);
            $qty = (int) $linePayload['qty'];
            $lineTotalRupiah = Money::fromInt((int) $linePayload['line_total_rupiah']);

            $this->assertStoreStockLineNotBelowMinimumSellingPrice(
                $product,
                $qty,
                $lineTotalRupiah,
            );

            $storeStockLines[] = StoreStockLine::create(
                $this->uuid->generate(),
                $product->id(),
                $qty,
                $lineTotalRupiah,
            );
        }

        return $storeStockLines;
    }

    private function getExistingProduct(string $productId): Product
    {
        $normalizedProductId = $this->normalizeRequired(
            $productId,
            'Product id pada store stock line wajib ada.'
        );

        $product = $this->products->getById($normalizedProductId);

        if ($product === null) {
            throw new DomainException('Product pada store stock line tidak ditemukan.');
        }

        return $product;
    }

    private function assertStoreStockLineNotBelowMinimumSellingPrice(
        Product $product,
        int $qty,
        Money $lineTotalRupiah,
    ): void {
        if ($qty <= 0) {
            throw new DomainException('Qty pada store stock line harus lebih besar dari nol.');
        }

        $minimumLineTotalRupiah = $product->hargaJual()->amount() * $qty;

        if ($lineTotalRupiah->amount() < $minimumLineTotalRupiah) {
            throw new DomainException('Harga jual pada store stock line tidak boleh di bawah harga jual minimum.');
        }
    }

    private function failureFromDomainException(DomainException $e): Result
    {
        $errorCode = $this->classifyErrorCode($e->getMessage());
        $errorKey = $this->classifyErrorKey($errorCode);

        return Result::failure(
            $e->getMessage(),
            [$errorKey => [$errorCode]]
        );
    }

    private function classifyErrorCode(string $message): string
    {
        if (str_contains($message, 'Stok inventory tidak cukup')) {
            return 'INVENTORY_INSUFFICIENT_STOCK';
        }

        if (str_contains($message, 'harga jual minimum')) {
            return 'PRICING_BELOW_MINIMUM_SELLING_PRICE';
        }

        if (str_contains($message, 'note yang sudah lunas')) {
            return 'NOTE_NEW_ITEMS_NOT_ALLOWED_AFTER_PAID';
        }

        return 'INVALID_WORK_ITEM';
    }

    private function classifyErrorKey(string $errorCode): string
    {
        if ($errorCode === 'INVENTORY_INSUFFICIENT_STOCK') {
            return 'inventory';
        }

        if ($errorCode === 'PRICING_BELOW_MINIMUM_SELLING_PRICE') {
            return 'pricing';
        }

        if ($errorCode === 'NOTE_NEW_ITEMS_NOT_ALLOWED_AFTER_PAID') {
            return 'note';
        }

        return 'work_item';
    }

    private function normalizeRequired(string $value, string $message): string
    {
        $normalized = trim($value);

        if ($normalized === '') {
            throw new DomainException($message);
        }

        return $normalized;
    }
}
