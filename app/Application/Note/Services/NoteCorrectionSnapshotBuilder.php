<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Core\Note\Note\Note;
use App\Core\Note\WorkItem\WorkItem;

final class NoteCorrectionSnapshotBuilder
{
    /**
     * @return array<string, mixed>
     */
    public function build(Note $note): array
    {
        return [
            'note' => [
                'id' => $note->id(),
                'customer_name' => $note->customerName(),
                'transaction_date' => $note->transactionDate()->format('Y-m-d'),
                'total_rupiah' => $note->totalRupiah()->amount(),
            ],
            'work_items' => array_map(
                static fn (WorkItem $item): array => [
                    'id' => $item->id(),
                    'line_no' => $item->lineNo(),
                    'transaction_type' => $item->transactionType(),
                    'status' => $item->status(),
                    'subtotal_rupiah' => $item->subtotalRupiah()->amount(),
                ],
                $note->workItems(),
            ),
        ];
    }
}
