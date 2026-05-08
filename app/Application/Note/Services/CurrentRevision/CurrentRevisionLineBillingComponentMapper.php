<?php

declare(strict_types=1);

namespace App\Application\Note\Services\CurrentRevision;

use App\Core\Note\Revision\NoteRevisionLineSnapshot;
use App\Core\Note\WorkItem\WorkItem;
use App\Core\Payment\PaymentComponentAllocation\PaymentComponentType;

final class CurrentRevisionLineBillingComponentMapper
{
    public function __construct(
        private readonly CurrentRevisionPayloadLineExtractor $lines,
        private readonly CurrentRevisionServiceAmountResolver $serviceAmounts,
    ) {
    }

    /** @param array<string, mixed> $payload */
    public function map(NoteRevisionLineSnapshot $line, array $payload): array
    {
        $workItemId = $line->workItemRootId() ?? $line->id();

        return match ($line->transactionType()) {
            WorkItem::TYPE_STORE_STOCK_SALE_ONLY => [
                $this->component(PaymentComponentType::PRODUCT_ONLY_WORK_ITEM, $workItemId, $line->subtotalRupiah(), 1),
            ],
            WorkItem::TYPE_SERVICE_WITH_STORE_STOCK_PART => $this->partsAndServiceFee(
                $line,
                $payload,
                $this->lines->storeStockLines($payload),
                PaymentComponentType::SERVICE_STORE_STOCK_PART,
                $workItemId,
            ),
            WorkItem::TYPE_SERVICE_WITH_EXTERNAL_PURCHASE => $this->partsAndServiceFee(
                $line,
                $payload,
                $this->lines->externalPurchaseLines($payload),
                PaymentComponentType::SERVICE_EXTERNAL_PURCHASE_PART,
                $workItemId,
            ),
            WorkItem::TYPE_SERVICE_ONLY => [
                $this->component(
                    PaymentComponentType::SERVICE_FEE,
                    $workItemId,
                    $this->serviceAmounts->resolve($line, $payload, []),
                    1,
                ),
            ],
            default => [],
        };
    }

    /**
     * @param array<string, mixed> $payload
     * @param list<array<string, mixed>> $partLines
     * @return list<array<string, int|string>>
     */
    private function partsAndServiceFee(
        NoteRevisionLineSnapshot $line,
        array $payload,
        array $partLines,
        string $partComponentType,
        string $workItemId,
    ): array {
        $components = [];
        $order = 1;

        foreach ($partLines as $partLine) {
            $amount = (int) ($partLine['line_total_rupiah'] ?? 0);
            $refId = trim((string) ($partLine['id'] ?? ''));

            if ($refId === '' || $amount <= 0) {
                continue;
            }

            $components[] = $this->component($partComponentType, $refId, $amount, $order++);
        }

        $serviceAmount = $this->serviceAmounts->resolve($line, $payload, $components);
        if ($serviceAmount > 0) {
            $components[] = $this->component(PaymentComponentType::SERVICE_FEE, $workItemId, $serviceAmount, $order);
        }

        return $components;
    }

    /** @return array<string, int|string> */
    private function component(string $type, string $refId, int $totalRupiah, int $order): array
    {
        return [
            'component_type' => $type,
            'component_ref_id' => $refId,
            'component_total_rupiah' => $totalRupiah,
            'component_order' => $order,
        ];
    }
}
