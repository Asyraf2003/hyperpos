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

        $this->rowSettlements->build($note->id(), $note->workItems());

        $workspacePanel = $this->workspacePanel->build($noteId);

        if ($workspacePanel === null) {
            return null;
        }

        $rows = $workspacePanel['rows'];
        $openLineCount = (int) ($workspacePanel['line_summary']['open_count'] ?? 0);
        $closeLineCount = (int) ($workspacePanel['line_summary']['close_count'] ?? 0);
        $paymentRows = $this->filterRows($rows, 'can_pay');
        $refundRows = $this->filterRows($rows, 'can_refund');
        $refundPaymentOptions = $this->refundPaymentOptions->build($note->id());
        $canShowPaymentAction = $isOpen = $note->isOpen()
            && $openLineCount > 0
            && $operational['outstanding_rupiah'] > 0
            && $paymentRows !== [];
        $canShowRefundAction = $refundRows !== [] && $refundPaymentOptions !== [];
        $isClosed = $note->isClosed();
        $isRefunded = $note->isRefunded();

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
                'can_show_payment_form' => $canShowPaymentAction,
                'can_show_refund_form' => $closeLineCount > 0,
                'can_show_payment_action' => $canShowPaymentAction,
                'can_show_refund_action' => $canShowRefundAction,
                'payment_rows' => $paymentRows,
                'refund_rows' => $refundRows,
                'refund_payment_options' => $refundPaymentOptions,
                'detail_action_contract' => [
                    'selection_mode' => 'modal_only',
                    'payment_flow' => 'launcher_then_modal_selection',
                    'refund_flow' => 'launcher_then_modal_selection',
                ],
                'can_show_correction_actions' => false,
                'correction_notice' => $isClosed
                    ? 'Nota sudah close. Pembalikan dilakukan lewat refund flow.'
                    : ($isRefunded ? 'Nota sudah refunded. Workspace tidak dipakai lagi.' : null),
                'line_summary' => $workspacePanel['line_summary'],
                'rows' => $rows,
                'correction_history' => $this->history->build($note->id()),
            ],
            'productOptions' => $this->products->build(),
        ];
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @return list<array<string, mixed>>
     */
    private function filterRows(array $rows, string $eligibilityKey): array
    {
        return array_values(array_filter(
            $rows,
            static fn (array $row): bool => (bool) ($row[$eligibilityKey] ?? false)
        ));
    }
}
