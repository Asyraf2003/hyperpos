<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Ports\Out\Note\NoteReaderPort;

final class NoteDetailPageDataBuilder
{
    public function __construct(
        private readonly NoteReaderPort $notes,
        private readonly NoteOperationalStatusEvaluator $operationalStatuses,
        private readonly NotePaymentStatusResolver $paymentStatuses,
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

        $workspacePanel = $this->workspacePanel->build($noteId);
        if ($workspacePanel === null) return null;

        $totals = (array) ($workspacePanel['note_totals'] ?? []);
        $grandTotal = (int) ($totals['grand_total_rupiah'] ?? 0);
        $allocated = (int) ($totals['total_allocated_rupiah'] ?? 0);
        $refunded = (int) ($totals['total_refunded_rupiah'] ?? 0);
        $netPaid = (int) ($totals['net_paid_rupiah'] ?? 0);
        $outstanding = (int) ($totals['outstanding_rupiah'] ?? max($grandTotal - $netPaid, 0));
        $status = $this->operationalStatuses->resolve($grandTotal, $netPaid);

        $operational = [
            'operational_status' => $status,
            'is_open' => $status === NoteOperationalStatusEvaluator::STATUS_OPEN,
            'is_close' => $status === NoteOperationalStatusEvaluator::STATUS_CLOSE,
            'grand_total_rupiah' => $grandTotal,
            'total_allocated_rupiah' => $allocated,
            'total_refunded_rupiah' => $refunded,
            'net_paid_rupiah' => $netPaid,
            'outstanding_rupiah' => $outstanding,
        ];

        $billingRows = $this->billingProjection->buildFromWorkspaceRows($workspacePanel['rows'] ?? []);
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

        $currentRevision = $this->revisionResolver->hasRevision($note->id())
            ? $this->revisionResolver->resolveOrFail($note->id())
            : null;

        $customerName = $currentRevision?->customerName() ?? $note->customerName();
        $customerPhone = $currentRevision?->customerPhone() ?? $note->customerPhone();
        $transactionDate = $currentRevision?->transactionDate()->format('Y-m-d') ?? $note->transactionDate()->format('Y-m-d');
        $revisionTimeline = $currentRevision !== null
            ? $this->revisionTimeline->build($currentRevision, $this->revisionResolver->timeline($note->id()))
            : [];

        return [
            'pageTitle' => 'Detail Nota',
            'workspace_panel' => $workspacePanel,
            'note' => $this->notePayloads->build(
                [
                    'id' => $note->id(),
                    'customer_name' => $customerName,
                    'customer_phone' => $customerPhone,
                    'transaction_date' => $transactionDate,
                    'note_state' => $note->noteState(),
                    'payment_status' => $this->paymentStatuses->resolve($grandTotal, $netPaid),
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
