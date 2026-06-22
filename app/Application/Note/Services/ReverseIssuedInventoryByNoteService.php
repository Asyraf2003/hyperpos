<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Application\Inventory\Services\ReverseIssuedInventoryOperation;
use App\Core\Note\Note\Note;
use App\Ports\Out\Inventory\InventoryMovementReaderPort;
use DateTimeImmutable;

final class ReverseIssuedInventoryByNoteService
{
    private const REFUND_REVERSE_SOURCE_TYPE = 'work_item_store_stock_line_reversal';

    public function __construct(
        private readonly ReverseIssuedInventoryOperation $reverseIssuedInventory,
        private readonly InventoryMovementReaderPort $movements,
    ) {
    }

    public function execute(
        Note $note,
        DateTimeImmutable $date,
        string $reverseSourceType = 'transaction_workspace_updated',
    ): int {
        $reversedCount = 0;

        foreach ($note->workItems() as $workItem) {
            foreach ($workItem->storeStockLines() as $line) {
                if ($this->alreadyReversedByRefund($line->id())) {
                    continue;
                }

                $reversedCount += count(
                    $this->reverseIssuedInventory->execute(
                        'work_item_store_stock_line',
                        $line->id(),
                        $date,
                        $reverseSourceType,
                    )
                );
            }
        }

        return $reversedCount;
    }

    private function alreadyReversedByRefund(string $storeStockLineId): bool
    {
        foreach ($this->movements->getBySource(self::REFUND_REVERSE_SOURCE_TYPE, $storeStockLineId) as $movement) {
            if ($movement->qtyDelta() > 0) {
                return true;
            }
        }

        return false;
    }
}
