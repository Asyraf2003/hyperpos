<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Application\Note\Services\RevisionWorkspace\RevisionSnapshotStoreStockLineTrustMarker;
use App\Core\Note\Note\Note;
use App\Ports\Out\Note\NoteWriterPort;

final class ApplyNoteRevisionAsActiveReplacement
{
    public function __construct(
        private readonly NoteWriterPort $notes,
        private readonly UpdateTransactionWorkspaceWorkItemPersister $workItems,
        private readonly NoteReplacementPaymentAllocationReconciler $payments,
        private readonly NoteHistoryProjectionService $projection,
        private readonly RevisionSnapshotStoreStockLineTrustMarker $snapshotTrust,
    ) {
    }

    /**
     * @param mixed $items
     */
    public function apply(Note $root, Note $replacement, mixed $items): void
    {
        $paymentAmounts = $this->payments->captureAllocatedAmounts($root->id());

        $root->updateHeader(
            $replacement->customerName(),
            $replacement->customerPhone(),
            $replacement->transactionDate(),
            $replacement->operationalNote(),
        );

        $trustedItems = $this->snapshotTrust->mark(
            is_array($items) ? array_values($items) : [],
            null,
            $root->workItems(),
        );

        $this->notes->updateHeader($root);
        $this->payments->deleteExisting($root->id());
        $this->workItems->persist($root, $trustedItems, $root->transactionDate());
        $this->notes->updateTotal($root);
        $this->payments->rebuild($root, $paymentAmounts);
        $this->projection->syncNote($root->id());
    }
}
