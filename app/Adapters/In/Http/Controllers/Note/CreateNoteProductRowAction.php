<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Note;

use App\Application\Note\Services\NoteProductSaleOnlyLineTotalResolver;
use App\Application\Note\UseCases\AddWorkItemHandler;
use App\Application\Shared\DTO\Result;
use App\Core\Note\WorkItem\WorkItem;

final class CreateNoteProductRowAction
{
    public function __construct(
        private readonly AddWorkItemHandler $addWorkItem,
        private readonly NoteProductSaleOnlyLineTotalResolver $lineTotals,
    ) {
    }

    /**
     * @param array<string, mixed> $row
     */
    public function __invoke(string $noteId, int $lineNo, array $row): Result
    {
        $productId = (string) ($row['product_id'] ?? '');
        $qty = (int) ($row['qty'] ?? 0);
        $lineTotalRupiah = $this->lineTotals->resolve($productId, $qty);

        if ($lineTotalRupiah === null) {
            return Result::failure(
                'Produk pada baris nota tidak ditemukan.',
                ['note' => ['PRODUCT_NOT_FOUND']]
            );
        }

        return $this->addWorkItem->handle(
            $noteId,
            $lineNo,
            WorkItem::TYPE_STORE_STOCK_SALE_ONLY,
            [],
            [],
            [[
                'product_id' => $productId,
                'qty' => $qty,
                'line_total_rupiah' => $lineTotalRupiah,
            ]]
        );
    }
}
