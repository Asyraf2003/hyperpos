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
        private readonly EditTransactionWorkspaceRouteNames $routes,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function build(string $noteId, string $routeArea = 'cashier'): array
    {
        $normalized = trim($noteId);

        $this->guard->assertWorkspaceEditPageAccessible($normalized);

        $note = $this->notes->getById($normalized);

        if ($note === null) {
            throw new DomainException('Nota tidak ditemukan.');
        }

        $currentRevision = $this->revisionResolver->resolveOrFail($normalized);

        $workspacePanel = $this->workspacePanel->build($normalized);

        if ($workspacePanel === null) {
            throw new DomainException('Panel workspace nota tidak ditemukan.');
        }

        $routeNames = $this->routes->resolve($routeArea);

        $oldNote = [
            'customer_name' => $currentRevision->customerName(),
            'customer_phone' => $currentRevision->customerPhone() ?? '',
            'transaction_date' => date('Y-m-d'),
        ];

        $oldItems = $this->revisionItems->mapMany($currentRevision);

        return [
            'pageTitle' => 'Edit Nota',
            'workspaceMode' => 'edit',
            'formAction' => route($routeNames['workspace_update'], ['noteId' => $normalized]),
            'cancelAction' => route($routeNames['show'], ['noteId' => $normalized]),
            'refundAction' => route($routeNames['refunds_store'], ['noteId' => $normalized]),
            'refundDateDefault' => date('Y-m-d'),
            'refundPaymentOptions' => $this->refundPaymentOptions->build($note->id()),
            'workspaceRefundRows' => [],
            'canShowRefundModal' => false,
            'oldNote' => $oldNote,
            'oldItems' => $oldItems,
            'defaultCustomerName' => $oldNote['customer_name'],
            'productLookupEndpoint' => route($routeNames['products_lookup']),
            'draftLoadEndpoint' => route($routeNames['draft_show']),
            'draftSaveEndpoint' => route($routeNames['draft_save']),
            'workspaceConfigJson' => json_encode([
                'oldItems' => $oldItems,
                'defaultCustomerName' => $oldNote['customer_name'],
                'productLookupEndpoint' => route($routeNames['products_lookup']),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}',
        ] + $this->options->build();
    }
}
