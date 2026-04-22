<?php

declare(strict_types=1);

namespace App\Application\Note\UseCases;

use App\Core\Note\Note\Note;
use App\Core\Note\WorkItem\WorkItem;
use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;

final class CreateNoteRevisionPayloadNoteBuilder
{
    public function __construct(
        private readonly CreateNoteRevisionServiceItemBuilder $services,
        private readonly CreateNoteRevisionProductItemBuilder $products,
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
        $workItems = $this->buildWorkItems($noteRootId, array_values((array) ($payload['items'] ?? [])));

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
     * @param list<array<string, mixed>> $itemsData
     * @return list<WorkItem>
     */
    private function buildWorkItems(string $noteRootId, array $itemsData): array
    {
        $workItems = [];
        $lineNo = 1;

        foreach ($itemsData as $item) {
            $mapped = $this->mapWorkItem($noteRootId, $lineNo, $item);

            if ($mapped !== null) {
                $workItems[] = $mapped;
                $lineNo++;
            }
        }

        return $workItems;
    }

    /**
     * @param array<string, mixed> $item
     */
    private function mapWorkItem(string $noteRootId, int $lineNo, array $item): ?WorkItem
    {
        return match ((string) ($item['line_type'] ?? '')) {
            'service' => $this->services->build($noteRootId, $lineNo, $item),
            'product' => $this->products->build($noteRootId, $lineNo, $item),
            default => null,
        };
    }
}
