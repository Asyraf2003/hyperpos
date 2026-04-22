<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Core\Shared\Exceptions\DomainException;
use App\Ports\Out\Note\NoteReaderPort;

final class EditTransactionWorkspacePageDataBuilder
{
    public function __construct(
        private readonly EditableWorkspaceNoteGuard $guard,
        private readonly NoteReaderPort $notes,
        private readonly NoteCurrentRevisionResolver $revisionResolver,
        private readonly NoteRevisionWorkspaceExistingItemMapper $revisionItems,
        private readonly CreateTransactionWorkspacePageDataBuilder $options,
        private readonly NoteWorkspacePanelDataBuilder $workspacePanel,
        private readonly NoteRefundPaymentOptionsBuilder $refundPaymentOptions,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function build(string $noteId): array
    {
        $normalized = trim($noteId);

        $this->guard->assertEditable($normalized);

        $note = $this->notes->getById($normalized);

        if ($note === null) {
            throw new DomainException('Nota tidak ditemukan.');
        }

        $currentRevision = $this->revisionResolver->resolveOrFail($normalized);

        $workspacePanel = $this->workspacePanel->build($normalized);

        if ($workspacePanel === null) {
            throw new DomainException('Panel workspace nota tidak ditemukan.');
        }

        $oldNote = [
            'customer_name' => $currentRevision->customerName(),
            'customer_phone' => $currentRevision->customerPhone() ?? '',
            'transaction_date' => $currentRevision->transactionDate()->format('Y-m-d'),
        ];

        $oldItems = $this->revisionItems->mapMany($currentRevision);

        $refundRows = array_values(array_filter(
            $workspacePanel['rows'] ?? [],
            static fn (array $row): bool => (bool) ($row['can_refund'] ?? false)
        ));

        return [
            'pageTitle' => 'Edit Nota',
            'workspaceMode' => 'edit',
            'formAction' => route('cashier.notes.workspace.update', ['noteId' => $normalized]),
            'cancelAction' => route('cashier.notes.show', ['noteId' => $normalized]),
            'refundAction' => route('cashier.notes.refunds.store', ['noteId' => $normalized]),
            'refundDateDefault' => date('Y-m-d'),
            'refundPaymentOptions' => $this->refundPaymentOptions->build($note->id()),
            'workspaceRefundRows' => $refundRows,
            'canShowRefundModal' => count($refundRows) > 0,
            'oldNote' => $oldNote,
            'oldItems' => $oldItems,
            'defaultCustomerName' => $oldNote['customer_name'],
            'productLookupEndpoint' => route('cashier.notes.products.lookup'),
            'workspaceConfigJson' => json_encode([
                'oldItems' => $oldItems,
                'defaultCustomerName' => $oldNote['customer_name'],
                'productLookupEndpoint' => route('cashier.notes.products.lookup'),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}',
        ] + $this->options->build();
    }
}
