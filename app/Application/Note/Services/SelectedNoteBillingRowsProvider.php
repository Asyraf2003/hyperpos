<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Application\Shared\DTO\Result;
use App\Core\Shared\Exceptions\DomainException;
use App\Ports\Out\Note\NoteReaderPort;

final class SelectedNoteBillingRowsProvider
{
    public function __construct(
        private readonly NoteReaderPort $notes,
        private readonly NoteWorkspacePanelDataBuilder $workspacePanels,
        private readonly NoteBillingProjectionBuilder $billingProjection,
    ) {
    }

    public function provide(string $noteId): Result
    {
        $note = $this->notes->getById(trim($noteId));

        if ($note === null) {
            return Result::failure('Nota tidak ditemukan.', ['payment' => ['PAYMENT_INVALID_TARGET']]);
        }

        try {
            $workspace = $this->workspacePanels->build($note->id());
        } catch (DomainException) {
            return Result::failure('Nota tidak memiliki workspace pembayaran yang valid.', ['payment' => ['PAYMENT_INVALID_TARGET']]);
        }

        if (! is_array($workspace)) {
            return Result::failure('Nota tidak memiliki workspace pembayaran yang valid.', ['payment' => ['PAYMENT_INVALID_TARGET']]);
        }

        $workspaceRows = $workspace['rows'] ?? null;

        if (! is_array($workspaceRows)) {
            return Result::failure('Nota tidak memiliki billing row pembayaran yang valid.', ['payment' => ['PAYMENT_INVALID_TARGET']]);
        }

        return Result::success($this->billingProjection->buildFromWorkspaceRows($workspaceRows));
    }
}
