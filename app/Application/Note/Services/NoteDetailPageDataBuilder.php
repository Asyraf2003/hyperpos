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
        private readonly NoteBillingProjectionBuilder $billingProjection,
        private readonly NotePseudoVersioningBuilder $pseudoVersioning,
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

        $this->rowSettlements->build($note->id(), $note->workItems());

        $workspacePanel = $this->workspacePanel->build($noteId);

        if ($workspacePanel === null) {
            return null;
        }

        $billingRows = $this->billingProjection->build($note->id()) ?? [];
        $history = $this->history->build($note->id());

        $openLineCount = (int) ($workspacePanel['line_summary']['open_count'] ?? 0);
        $closeLineCount = (int) ($workspacePanel['line_summary']['close_count'] ?? 0);
        $isOpen = $note->isOpen();
        $isClosed = $note->isClosed();
        $isRefunded = $note->isRefunded();
        $refundPaymentOptions = $this->refundPaymentOptions->build($note->id());
        $refundRows = array_values(array_filter(
            $workspacePanel['rows'],
            static fn (array $row): bool => (bool) ($row['can_refund'] ?? false)
        ));

        $hasOutstandingBillingRow = count(array_filter(
            $billingRows,
            static fn (array $row): bool => (int) ($row['outstanding_rupiah'] ?? 0) > 0
        )) > 0;

        $pseudoVersioning = $this->pseudoVersioning->build([
            'note_state' => $note->noteState(),
            'grand_total_rupiah' => $operational['grand_total_rupiah'],
            'net_paid_rupiah' => $operational['net_paid_rupiah'],
            'total_refunded_rupiah' => $operational['total_refunded_rupiah'],
            'outstanding_rupiah' => $operational['outstanding_rupiah'],
            'refund_required_rupiah' => max($operational['net_paid_rupiah'] - $operational['grand_total_rupiah'], 0),
        ], $workspacePanel['line_summary'], $history);

        return [
            'pageTitle' => 'Detail Nota',
            'workspace_panel' => $workspacePanel,
            'note' => [
                'id' => $note->id(),
                'customer_name' => $note->customerName(),
                'customer_phone' => $note->customerPhone(),
                'transaction_date' => $note->transactionDate()->format('Y-m-d'),
                'note_state' => $note->noteState(),
                'operational_status' => $operational['operational_status'],
                'is_open' => $isOpen,
                'is_closed' => $isClosed,
                'is_refunded' => $isRefunded,
                'grand_total_rupiah' => $operational['grand_total_rupiah'],
                'total_allocated_rupiah' => $operational['total_allocated_rupiah'],
                'total_refunded_rupiah' => $operational['total_refunded_rupiah'],
                'net_paid_rupiah' => $operational['net_paid_rupiah'],
                'outstanding_rupiah' => $operational['outstanding_rupiah'],
                'refund_required_rupiah' => max($operational['net_paid_rupiah'] - $operational['grand_total_rupiah'], 0),
                'payment_status' => $paymentStatus,
                'can_add_rows' => $isOpen,
                'can_show_edit_actions' => $isOpen,
                'can_edit_workspace' => $isOpen,
                'can_show_workspace_panel' => $isOpen || $isClosed,
                'can_show_payment_form' => $isOpen && $openLineCount > 0 && $hasOutstandingBillingRow,
                'can_show_refund_form' => $closeLineCount > 0 && $refundPaymentOptions !== [] && $refundRows !== [],
                'refund_payment_options' => $refundPaymentOptions,
                'can_show_correction_actions' => false,
                'correction_notice' => $isClosed
                    ? 'Nota sudah close. Pembalikan dilakukan lewat refund flow.'
                    : ($isRefunded ? 'Nota sudah refunded. Workspace tidak dipakai lagi.' : null),
                'line_summary' => $workspacePanel['line_summary'],
                'rows' => $workspacePanel['rows'],
                'refund_rows' => $refundRows,
                'billing_rows' => $billingRows,
                'pseudo_versioning' => $pseudoVersioning,
                'correction_history' => $history,
            ],
            'productOptions' => $this->products->build(),
        ];
    }
}
