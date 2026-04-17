<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Ports\Out\Note\NoteReaderPort;

final class NoteDetailPageDataBuilder
{
    public function __construct(
        private readonly NoteReaderPort $notes,
        private readonly NoteOperationalStatusResolver $operationalStatuses,
        private readonly NotePaymentStatusResolver $paymentStatuses,
        private readonly NoteOperationalRowSettlementProjector $rowSettlements,
        private readonly NoteRefundPaymentOptionsBuilder $refundPaymentOptions,
        private readonly NoteProductOptionsBuilder $products,
        private readonly NoteCorrectionHistoryBuilder $history,
        private readonly NoteWorkspacePanelDataBuilder $workspacePanel,
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

        // legacy calculation kept temporarily for transition safety
        $this->rowSettlements->build($note->id(), $note->workItems());

        // new line-centric workspace source
        $workspacePanel = $this->workspacePanel->build($noteId);

        if ($workspacePanel === null) {
            return null;
        }

        return [
            'pageTitle' => 'Detail Nota',
            'workspace_panel' => $workspacePanel,
            'note' => [
                'id' => $note->id(),
                'customer_name' => $note->customerName(),
                'customer_phone' => $note->customerPhone(),
                'transaction_date' => $note->transactionDate()->format('Y-m-d'),
                'note_state' => $note->noteState(),

                // legacy note-centric fields kept temporarily
                'operational_status' => $operational['operational_status'],
                'is_open' => $operational['is_open'],
                'is_closed' => $operational['is_close'],
                'grand_total_rupiah' => $operational['grand_total_rupiah'],
                'total_allocated_rupiah' => $operational['total_allocated_rupiah'],
                'total_refunded_rupiah' => $operational['total_refunded_rupiah'],
                'net_paid_rupiah' => $operational['net_paid_rupiah'],
                'outstanding_rupiah' => $operational['outstanding_rupiah'],
                'refund_required_rupiah' => max($operational['net_paid_rupiah'] - $operational['grand_total_rupiah'], 0),
                'payment_status' => $paymentStatus,
                'can_add_rows' => $operational['is_open'],
                'can_show_edit_actions' => $operational['is_open'],
                'can_edit_workspace' => $operational['is_open'],
                'can_show_payment_form' => $operational['is_open'] && $operational['outstanding_rupiah'] > 0,
                'can_show_refund_form' => $operational['is_close'],
                'refund_payment_options' => $this->refundPaymentOptions->build($note->id()),
                'can_show_correction_actions' => false,
                'correction_notice' => $operational['is_close']
                    ? 'Nota sudah close. Pembalikan dilakukan lewat refund flow.'
                    : null,

                // new line-centric transition fields
                'line_summary' => $workspacePanel['line_summary'],
                'rows' => $workspacePanel['rows'],

                // legacy history remains
                'correction_history' => $this->history->build($note->id()),
            ],
            'productOptions' => $this->products->build(),
        ];
    }
}
