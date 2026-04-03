<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Core\Note\WorkItem\WorkItem;
use App\Ports\Out\Note\NoteReaderPort;
use App\Ports\Out\Payment\CustomerRefundReaderPort;
use App\Ports\Out\Payment\PaymentAllocationReaderPort;

final class NoteDetailPageDataBuilder
{
    public function __construct(
        private readonly NoteReaderPort $notes,
        private readonly PaymentAllocationReaderPort $allocations,
        private readonly CustomerRefundReaderPort $refunds,
        private readonly NotePaymentStatusResolver $statuses,
        private readonly NoteRowSettlementSummaryBuilder $rowSettlements,
        private readonly NoteProductOptionsBuilder $products,
        private readonly NoteCorrectionHistoryBuilder $history,
    ) {
    }

    public function build(string $noteId): ?array
    {
        $note = $this->notes->getById(trim($noteId));
        if ($note === null) return null;

        $grandTotal = $note->totalRupiah()->amount();
        $allocated = $this->allocations->getTotalAllocatedAmountByNoteId($note->id())->amount();
        $refunded = $this->refunds->getTotalRefundedAmountByNoteId($note->id())->amount();
        $netPaid = max($allocated - $refunded, 0);
        $status = $this->statuses->resolve($grandTotal, $netPaid);
        $isOpen = $note->isOpen();
        $isClosed = $note->isClosed();

        $rowSettlements = $this->rowSettlements->build($note->id(), $note->workItems());

        return [
            'pageTitle' => 'Detail Nota',
            'note' => [
                'id' => $note->id(),
                'customer_name' => $note->customerName(),
                'customer_phone' => $note->customerPhone(),
                'transaction_date' => $note->transactionDate()->format('Y-m-d'),
                'note_state' => $note->noteState(),
                'is_open' => $isOpen,
                'is_closed' => $isClosed,
                'grand_total_rupiah' => $grandTotal,
                'total_allocated_rupiah' => $allocated,
                'total_refunded_rupiah' => $refunded,
                'net_paid_rupiah' => $netPaid,
                'outstanding_rupiah' => max($grandTotal - $netPaid, 0),
                'payment_status' => $status,
                'can_add_rows' => $status !== 'paid',
                'can_show_edit_actions' => $isOpen,
                'can_show_payment_form' => $isOpen && max($grandTotal - $netPaid, 0) > 0,
                'can_show_correction_actions' => $isOpen && $status === 'paid',
                'correction_notice' => $status === 'paid' ? 'Nota sudah lunas. Perubahan hanya boleh lewat correction flow.' : null,
                'rows' => $this->mapRows($note->workItems(), $rowSettlements),
                'correction_history' => $this->history->build($note->id()),
            ],
            'productOptions' => $this->products->build(),
        ];
    }

    /**
     * @param array<int, WorkItem> $rows
     * @return list<array<string, mixed>>
     */
    private function mapRows(array $rows, array $settlements): array
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
