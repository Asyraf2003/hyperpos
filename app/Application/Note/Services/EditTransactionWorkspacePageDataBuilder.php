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
        private readonly CreateTransactionWorkspacePageDataBuilder $options,
        private readonly TransactionWorkspaceExistingItemMapper $existingItems,
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

        $oldNote = [
            'customer_name' => $note->customerName(),
            'customer_phone' => $note->customerPhone() ?? '',
            'transaction_date' => $note->transactionDate()->format('Y-m-d'),
        ];

        $oldItems = $this->existingItems->mapMany($note);

        return [
            'pageTitle' => 'Edit Nota',
            'workspaceMode' => 'edit',
            'formAction' => route('cashier.notes.workspace.update', ['noteId' => $normalized]),
            'cancelAction' => route('cashier.notes.show', ['noteId' => $normalized]),
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
