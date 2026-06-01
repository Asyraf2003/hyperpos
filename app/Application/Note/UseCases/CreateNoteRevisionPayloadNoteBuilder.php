<?php

declare(strict_types=1);

namespace App\Application\Note\UseCases;

use App\Application\Note\Services\RevisionWorkspace\RevisionSnapshotStoreStockLineTrustMarker;
use App\Core\Note\Note\Note;
use App\Core\Note\Revision\NoteRevision;
use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;

final class CreateNoteRevisionPayloadNoteBuilder
{
    public function __construct(
        private readonly CreateNoteRevisionPayloadWorkItemBuilder $workItems,
        private readonly RevisionSnapshotStoreStockLineTrustMarker $snapshotTrust,
    ) {
    }

    /**
     * @param array{
     *   note: array<string, mixed>,
     *   items: list<array<string, mixed>>,
     *   reason?: string,
     *   inline_payment?: array<string, mixed>
     * } $payload
     */
    public function build(
        string $noteRootId,
        array $payload,
        ?NoteRevision $currentRevision = null,
        array $currentWorkItems = [],
    ): Note {
        $noteData = (array) ($payload['note'] ?? []);
        $items = $this->snapshotTrust->mark(
            array_values((array) ($payload['items'] ?? [])),
            $currentRevision,
            $currentWorkItems,
        );
        $workItems = $this->workItems->build($noteRootId, $items);

        if ($workItems === []) {
            throw new DomainException('Minimal satu item valid wajib ada untuk membuat revisi.');
        }

        $total = array_reduce(
            $workItems,
            fn (int $carry, object $item): int => $carry + $item->subtotalRupiah()->amount(),
            0,
        );

        return Note::rehydrate(
            $noteRootId,
            (string) ($noteData['customer_name'] ?? ''),
            isset($noteData['customer_phone']) ? (string) $noteData['customer_phone'] : null,
            new \DateTimeImmutable((string) ($noteData['transaction_date'] ?? '')),
            Money::fromInt($total),
            $workItems,
            Note::STATE_OPEN,
            null,
            null,
            null,
            null,
            null,
            isset($noteData['operational_note']) ? (string) $noteData['operational_note'] : null,
        );
    }
}
