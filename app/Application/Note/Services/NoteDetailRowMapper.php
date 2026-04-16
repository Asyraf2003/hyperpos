<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Core\Note\WorkItem\WorkItem;

final class NoteDetailRowMapper
{
    /**
     * @param array<int, WorkItem> $rows
     * @param array<string, array<string, mixed>> $settlements
     * @return list<array<string, mixed>>
     */
    public function map(array $rows, array $settlements): array
    {
        return array_map(
            function (WorkItem $item) use ($settlements): array {
                $settlement = $settlements[$item->id()] ?? [
                    'allocated_rupiah' => 0,
                    'refunded_rupiah' => 0,
                    'net_paid_rupiah' => 0,
                    'outstanding_rupiah' => $item->subtotalRupiah()->amount(),
                    'settlement_label' => 'hutang',
                ];

                return [
                    'id' => $item->id(),
                    'line_no' => $item->lineNo(),
                    'type_label' => $item->transactionType() === WorkItem::TYPE_STORE_STOCK_SALE_ONLY ? 'Produk' : 'Servis',
                    'transaction_type' => $item->transactionType(),
                    'can_correct_service_only' => $item->transactionType() === WorkItem::TYPE_SERVICE_ONLY,
                    'status' => $item->status(),
                    'subtotal_rupiah' => $item->subtotalRupiah()->amount(),
                    'allocated_rupiah' => $settlement['allocated_rupiah'],
                    'refunded_rupiah' => $settlement['refunded_rupiah'],
                    'net_paid_rupiah' => $settlement['net_paid_rupiah'],
                    'outstanding_rupiah' => $settlement['outstanding_rupiah'],
                    'settlement_label' => $settlement['settlement_label'],
                ];
            },
            $rows
        );
    }
}
