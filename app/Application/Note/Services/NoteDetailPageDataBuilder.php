<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Core\Note\WorkItem\WorkItem;
use App\Ports\Out\Note\NoteReaderPort;

final class NoteDetailPageDataBuilder
{
    public function __construct(
        private readonly NoteReaderPort $notes,
        private readonly NoteOperationalStatusResolver $operationalStatuses,
        private readonly NotePaymentStatusResolver $paymentStatuses,
        private readonly NoteRowSettlementSummaryBuilder $rowSettlements,
        private readonly NoteProductOptionsBuilder $products,
        private readonly NoteCorrectionHistoryBuilder $history,
    ) {
    }

    public function build(string $noteId): ?array
    {
        $note = $this->notes->getById(trim($noteId));

        if ($note === null) {
            return null;
        }

        $operational = $this->operationalStatuses->resolve($note);
        $paymentStatus = $this->paymentStatuses->resolve(
            $operational['grand_total_rupiah'],
            $operational['net_paid_rupiah'],
        );

        $rowSettlements = $this->rowSettlements->build($note->id(), $note->workItems());

        return [
            'pageTitle' => 'Detail Nota',
            'note' => [
                'id' => $note->id(),
                'customer_name' => $note->customerName(),
                'customer_phone' => $note->customerPhone(),
                'transaction_date' => $note->transactionDate()->format('Y-m-d'),
                'note_state' => $note->noteState(),
                'operational_status' => $operational['operational_status'],
                'is_open' => $operational['is_open'],
                'is_closed' => $operational['is_close'],
                'grand_total_rupiah' => $operational['grand_total_rupiah'],
                'total_allocated_rupiah' => $operational['total_allocated_rupiah'],
                'total_refunded_rupiah' => $operational['total_refunded_rupiah'],
                'net_paid_rupiah' => $operational['net_paid_rupiah'],
                'outstanding_rupiah' => $operational['outstanding_rupiah'],
                'payment_status' => $paymentStatus,
                'can_add_rows' => $operational['is_open'],
                'can_show_edit_actions' => $operational['is_open'],
                'can_edit_workspace' => $operational['is_open'],
                'can_show_payment_form' => $operational['is_open'] && $operational['outstanding_rupiah'] > 0,
                'can_show_correction_actions' => false,
                'correction_notice' => $operational['is_closed']
                    ? 'Nota sudah close. Pembalikan dilakukan lewat refund flow.'
                    : null,
                'rows' => $this->mapRows($note->workItems(), $rowSettlements),
                'correction_history' => $this->history->build($note->id()),
            ],
            'productOptions' => $this->products->build(),
        ];
    }

    /**
     * @param array<int, WorkItem> $rows
     * @param array<string, array<string, mixed>> $settlements
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
