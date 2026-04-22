<?php

declare(strict_types=1);

namespace App\Application\Note\UseCases;

use App\Core\Note\Note\Note;
use App\Core\Note\WorkItem\ServiceDetail;
use App\Core\Note\WorkItem\WorkItem;
use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;

final class CreateNoteRevisionPayloadNoteBuilder
{
    public function __construct(
        private readonly CreateNoteRevisionItemNormalizer $values,
    ) {
    }

    /**
     * @param array{
     *   note: array<string, mixed>,
     *   items: list<array<string, mixed>>,
     *   reason: string
     * } $payload
     */
    public function build(string $noteRootId, array $payload): Note
    {
        $noteData = (array) ($payload['note'] ?? []);
        $itemsData = array_values((array) ($payload['items'] ?? []));

        $workItems = [];
        $lineNo = 1;

        foreach ($itemsData as $item) {
            if (! is_array($item)) {
                continue;
            }

            $mapped = $this->mapWorkItem($noteRootId, $lineNo, $item);

            if ($mapped !== null) {
                $workItems[] = $mapped;
                $lineNo++;
            }
        }

        if ($workItems === []) {
            throw new DomainException('Minimal satu item valid wajib ada untuk membuat revisi.');
        }

        $total = array_reduce(
            $workItems,
            fn (int $carry, WorkItem $item): int => $carry + $item->subtotalRupiah()->amount(),
            0,
        );

        return Note::rehydrate(
            $noteRootId,
            (string) ($noteData['customer_name'] ?? ''),
            isset($noteData['customer_phone']) ? (string) $noteData['customer_phone'] : null,
            new \DateTimeImmutable((string) ($noteData['transaction_date'] ?? '')),
            Money::fromInt($total),
            $workItems,
            Note::STATE_OPEN,
        );
    }

    /**
     * @param array<string, mixed> $item
     */
    private function mapWorkItem(string $noteRootId, int $lineNo, array $item): ?WorkItem
    {
        $lineType = (string) ($item['line_type'] ?? '');

        if ($lineType === 'service') {
            $serviceName = $this->values->string($item['service_name'] ?? '', 'Service Revision');
            $servicePrice = $this->values->integer($item['service_price'] ?? '0');

            $service = ServiceDetail::create(
                $serviceName === '' ? 'Service Revision' : $serviceName,
                Money::fromInt($servicePrice),
                ServiceDetail::PART_SOURCE_NONE,
            );

            return WorkItem::createServiceOnly(
                sprintf('%s-wi-r%03d', $noteRootId, $lineNo),
                $noteRootId,
                $lineNo,
                $service,
                WorkItem::STATUS_OPEN,
            );
        }

        if ($lineType === 'product') {
            $qty = $this->values->positiveInteger($item['qty'] ?? '1');
            $price = $this->values->integer($item['price'] ?? '0');

            return WorkItem::createStoreStockSaleOnly(
                sprintf('%s-wi-r%03d', $noteRootId, $lineNo),
                $noteRootId,
                $lineNo,
                [[
                    'product_id' => (string) ($item['product_id'] ?? ''),
                    'qty' => $qty,
                    'selling_price_rupiah' => $price,
                    'subtotal_rupiah' => $qty * $price,
                    'note' => (string) ($item['note'] ?? ''),
                ]],
                WorkItem::STATUS_OPEN,
            );
        }

        return null;
    }
}
