<?php

declare(strict_types=1);

namespace App\Application\Inventory\Services;

use App\Ports\Out\Note\NoteReaderPort;
use DateTimeImmutable;

final class ReverseNoteStoreStockInventoryOperation
{
    public function __construct(
        private readonly NoteReaderPort $notes,
        private readonly ReverseIssuedInventoryOperation $reverseIssuedInventory,
    ) {
    }

    public function execute(string $noteId, DateTimeImmutable $date): void
    {
        $note = $this->notes->getById(trim($noteId));

        if ($note === null) {
            return;
        }

        foreach ($note->workItems() as $workItem) {
            foreach ($workItem->storeStockLines() as $line) {
                $this->reverseIssuedInventory->execute(
                    'work_item_store_stock_line',
                    $line->id(),
                    $date,
                    'work_item_store_stock_line_reversal'
                );
            }
        }
    }
}
