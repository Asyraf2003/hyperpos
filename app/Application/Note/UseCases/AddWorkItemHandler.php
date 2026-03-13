<?php

declare(strict_types=1);

namespace App\Application\Note\UseCases;

use App\Application\Shared\DTO\Result;
use App\Core\Note\WorkItem\ExternalPurchaseLine;
use App\Core\Note\WorkItem\ServiceDetail;
use App\Core\Note\WorkItem\WorkItem;
use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;
use App\Ports\Out\Note\NoteReaderPort;
use App\Ports\Out\Note\NoteWriterPort;
use App\Ports\Out\Note\WorkItemWriterPort;
use App\Ports\Out\TransactionManagerPort;
use App\Ports\Out\UuidPort;
use Throwable;

final class AddWorkItemHandler
{
    public function __construct(
        private readonly NoteReaderPort $notes,
        private readonly NoteWriterPort $noteWriter,
        private readonly WorkItemWriterPort $workItems,
        private readonly TransactionManagerPort $transactions,
        private readonly UuidPort $uuid,
    ) {
    }

    /**
     * @param array<string, mixed> $serviceDetailPayload
     * @param array<int, array<string, mixed>> $externalPurchaseLinesPayload
     */
    public function handle(
        string $noteId,
        int $lineNo,
        string $transactionType,
        array $serviceDetailPayload,
        array $externalPurchaseLinesPayload = [],
    ): Result {
        try {
            $normalizedNoteId = $this->normalizeRequired($noteId, 'Note id pada work item wajib ada.');
            $normalizedTransactionType = $this->normalizeRequired(
                $transactionType,
                'Transaction type pada work item wajib ada.'
            );
        } catch (DomainException $e) {
            return Result::failure(
                $e->getMessage(),
                ['work_item' => ['INVALID_WORK_ITEM']]
            );
        }

        $transactionStarted = false;

        try {
            $this->transactions->begin();
            $transactionStarted = true;

            $note = $this->notes->getById($normalizedNoteId);

            if ($note === null) {
                throw new DomainException('Note tidak ditemukan.');
            }

            $workItem = $this->buildWorkItem(
                $note->id(),
                $lineNo,
                $normalizedTransactionType,
                $serviceDetailPayload,
                $externalPurchaseLinesPayload,
            );

            $note->addWorkItem($workItem);

            $this->workItems->create($workItem);
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
                    'service_detail' => [
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
                ],
                'Work item berhasil ditambahkan ke note.'
            );
        } catch (DomainException $e) {
            if ($transactionStarted) {
                $this->transactions->rollBack();
            }

            return Result::failure(
                $e->getMessage(),
                ['work_item' => ['INVALID_WORK_ITEM']]
            );
        } catch (Throwable $e) {
            if ($transactionStarted) {
                $this->transactions->rollBack();
            }

            throw $e;
        }
    }

    /**
     * @param array<string, mixed> $serviceDetailPayload
     * @param array<int, array<string, mixed>> $externalPurchaseLinesPayload
     */
    private function buildWorkItem(
        string $noteId,
        int $lineNo,
        string $transactionType,
        array $serviceDetailPayload,
        array $externalPurchaseLinesPayload,
    ): WorkItem {
        $serviceDetail = $this->buildServiceDetail($serviceDetailPayload);

        if ($transactionType === WorkItem::TYPE_SERVICE_ONLY) {
            return WorkItem::createServiceOnly(
                $this->uuid->generate(),
                $noteId,
                $lineNo,
                $serviceDetail,
            );
        }

        if ($transactionType === WorkItem::TYPE_SERVICE_WITH_EXTERNAL_PURCHASE) {
            return WorkItem::createServiceWithExternalPurchase(
                $this->uuid->generate(),
                $noteId,
                $lineNo,
                $serviceDetail,
                $this->buildExternalPurchaseLines($externalPurchaseLinesPayload),
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

    private function normalizeRequired(string $value, string $message): string
    {
        $normalized = trim($value);

        if ($normalized === '') {
            throw new DomainException($message);
        }

        return $normalized;
    }
}
