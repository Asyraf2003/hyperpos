<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Core\Note\Note\Note;
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
        private readonly NoteDetailActionModalPayloadBuilder $actionPayloads,
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
            (int) $operational['grand_total_rupiah'],
            (int) $operational['net_paid_rupiah'],
        );

        $this->rowSettlements->build($note->id(), $note->workItems());
        $workspacePanel = $this->workspacePanel->build($noteId);

        if ($workspacePanel === null) {
            return null;
        }

        return [
            'pageTitle' => 'Detail Nota',
            'workspace_panel' => $workspacePanel,
            'note' => $this->buildNotePayload(
                $note,
                $operational,
                $paymentStatus,
                $workspacePanel,
            ),
            'productOptions' => $this->products->build(),
        ];
    }

    /**
     * @param array<string, mixed> $operational
     * @param array<string, mixed> $paymentStatus
     * @param array<string, mixed> $workspacePanel
     * @return array<string, mixed>
     */
    private function buildNotePayload(
        Note $note,
        array $operational,
        array $paymentStatus,
        array $workspacePanel,
    ): array {
        $isOpen = $note->isOpen();
        $isClosed = $note->isClosed();
        $isRefunded = $note->isRefunded();

        $actionPayloads = $this->actionPayloads->build(
            $isOpen,
            (array) $workspacePanel['rows'],
            $operational,
            (array) $workspacePanel['line_summary'],
            $this->refundPaymentOptions->build($note->id()),
        );

        return [
            'id' => $note->id(),
            'customer_name' => $note->customerName(),
            'customer_phone' => $note->customerPhone(),
            'transaction_date' => $note->transactionDate()->format('Y-m-d'),
            'note_state' => $note->noteState(),
            'operational_status' => $operational['operational_status'],
            'is_open' => $isOpen,
            'is_closed' => $isClosed,
            'is_refunded' => $isRefunded,
            'grand_total_rupiah' => (int) $operational['grand_total_rupiah'],
            'total_allocated_rupiah' => (int) $operational['total_allocated_rupiah'],
            'total_refunded_rupiah' => (int) $operational['total_refunded_rupiah'],
            'net_paid_rupiah' => (int) $operational['net_paid_rupiah'],
            'outstanding_rupiah' => (int) $operational['outstanding_rupiah'],
            'refund_required_rupiah' => max(
                (int) $operational['net_paid_rupiah'] - (int) $operational['grand_total_rupiah'],
                0
            ),
            'payment_status' => $paymentStatus,
            'can_add_rows' => $isOpen,
            'can_show_edit_actions' => $isOpen,
            'can_edit_workspace' => $isOpen,
            'can_show_workspace_panel' => $isOpen || $isClosed,
            'can_show_correction_actions' => false,
            'correction_notice' => $this->buildCorrectionNotice($isClosed, $isRefunded),
            'line_summary' => $workspacePanel['line_summary'],
            'rows' => $workspacePanel['rows'],
            'correction_history' => $this->history->build($note->id()),
            ...$actionPayloads,
        ];
    }

    private function buildCorrectionNotice(bool $isClosed, bool $isRefunded): ?string
    {
        if ($isClosed) {
            return 'Nota sudah close. Pembalikan dilakukan lewat refund flow.';
        }

        return $isRefunded ? 'Nota sudah refunded. Workspace tidak dipakai lagi.' : null;
    }
}
