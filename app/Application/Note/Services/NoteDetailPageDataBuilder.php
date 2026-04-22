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
        private readonly NoteCurrentRevisionResolver $revisionResolver,
        private readonly NoteDetailRevisionTimelineBuilder $revisionTimeline,
        private readonly NoteDetailNotePayloadBuilder $notePayloads,
    ) {
    }

    public function build(string $noteId): ?array
    {
        $note = $this->notes->getById(trim($noteId));
        if ($note === null) return null;

        $operational = $this->operationalStatuses->resolve($note);
        $this->rowSettlements->build($note->id(), $note->workItems());

        $workspacePanel = $this->workspacePanel->build($noteId);
        if ($workspacePanel === null) return null;

        $billingRows = $this->billingProjection->build($note->id()) ?? [];
        $history = $this->history->build($note->id());

        $refundRows = array_values(array_filter(
            $workspacePanel['rows'],
            static fn (array $row): bool => (bool) ($row['can_refund'] ?? false)
        ));

        $refundOptions = $this->refundPaymentOptions->build($note->id());

        $hasOutstanding = count(array_filter(
            $billingRows,
            static fn (array $row): bool => (int) ($row['outstanding_rupiah'] ?? 0) > 0
        )) > 0;

        $currentRevision = $this->revisionResolver->resolveOrFail($note->id());
        $revisionTimeline = $this->revisionTimeline->build(
            $currentRevision,
            $this->revisionResolver->timeline($note->id()),
        );

        return [
            'pageTitle' => 'Detail Nota',
            'workspace_panel' => $workspacePanel,
            'note' => $this->notePayloads->build(
                [
                    'id' => $note->id(),
                    'customer_name' => $currentRevision->customerName(),
                    'customer_phone' => $currentRevision->customerPhone(),
                    'transaction_date' => $currentRevision->transactionDate()->format('Y-m-d'),
                    'note_state' => $note->noteState(),
                    'payment_status' => $this->paymentStatuses->resolve(
                        $operational['grand_total_rupiah'],
                        $operational['net_paid_rupiah'],
                    ),
                ],
                $workspacePanel,
                $operational,
                $refundOptions,
                $refundRows,
                $billingRows,
                $revisionTimeline,
                $history,
                $note->isOpen(),
                $note->isClosed(),
                $note->isRefunded(),
                $hasOutstanding
            ),
            'productOptions' => $this->products->build(),
        ];
    }
}
