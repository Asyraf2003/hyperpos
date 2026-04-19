<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Application\Inventory\Services\ReverseIssuedInventoryOperation;
use App\Core\Note\Note\Note;
use DateTimeImmutable;

final class ReverseIssuedInventoryByNoteService
{
    public function __construct(
        private readonly ReverseIssuedInventoryOperation $reverseIssuedInventory,
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
}
