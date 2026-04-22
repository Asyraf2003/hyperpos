<?php

declare(strict_types=1);

namespace App\Application\Note\UseCases;

use App\Core\Note\WorkItem\WorkItem;

final class CreateNoteRevisionProductItemBuilder
{
    public function __construct(
        private readonly CreateNoteRevisionItemNormalizer $values,
    ) {
    }

    /**
     * @param array<string, mixed> $item
     */
    public function build(string $noteRootId, int $lineNo, array $item): WorkItem
    {
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
}
