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
        private readonly NotePaymentStatusResolver $statusResolver,
    ) {
    }

    public function build(string $noteId): ?array
    {
        $note = $this->notes->getById(trim($noteId));

        if ($note === null) {
            return null;
        }

        $grandTotal = $note->totalRupiah()->amount();
        $allocated = $this->allocations->getTotalAllocatedAmountByNoteId($note->id())->amount();
        $refunded = $this->refunds->getTotalRefundedAmountByNoteId($note->id())->amount();
        $netSettlement = max($allocated - $refunded, 0);
        $outstanding = max($grandTotal - $netSettlement, 0);

        return [
            'pageTitle' => 'Detail Nota',
            'noteId' => $note->id(),
            'note' => [
                'id' => $note->id(),
                'customer_name_current' => $note->customerName(),
                'customer_name_original' => null,
                'customer_phone_current' => null,
                'customer_phone_original' => null,
                'transaction_date' => $note->transactionDate()->format('Y-m-d'),
                'grand_total_rupiah' => $grandTotal,
                'total_allocated_rupiah' => $allocated,
                'total_refunded_rupiah' => $refunded,
                'net_paid_rupiah' => $netSettlement,
                'outstanding_rupiah' => $outstanding,
                'payment_status' => $this->statusResolver->resolve($grandTotal, $netSettlement),
                'rows' => $this->mapRows($note->workItems()),
            ],
        ];
    }

    /**
     * @param array<int, WorkItem> $rows
     * @return list<array<string, mixed>>
     */
    private function mapRows(array $rows): array
    {
        return array_map(
            static fn (WorkItem $item): array => [
                'id' => $item->id(),
                'line_no' => $item->lineNo(),
                'transaction_type' => $item->transactionType(),
                'status' => $item->status(),
                'subtotal_rupiah' => $item->subtotalRupiah()->amount(),
            ],
            $rows
        );
    }
}
