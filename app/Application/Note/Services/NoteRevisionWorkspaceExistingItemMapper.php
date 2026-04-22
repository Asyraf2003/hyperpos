<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Application\Note\Services\RevisionWorkspace\RevisionWorkspaceProductOnlyMapper;
use App\Application\Note\Services\RevisionWorkspace\RevisionWorkspaceServiceExternalMapper;
use App\Application\Note\Services\RevisionWorkspace\RevisionWorkspaceServiceOnlyMapper;
use App\Application\Note\Services\RevisionWorkspace\RevisionWorkspaceServiceStoreStockMapper;
use App\Core\Note\Revision\NoteRevision;
use App\Core\Note\Revision\NoteRevisionLineSnapshot;
use App\Core\Shared\Exceptions\DomainException;

final class NoteRevisionWorkspaceExistingItemMapper
{
    public function __construct(
        private readonly RevisionWorkspaceServiceOnlyMapper $serviceOnly,
        private readonly RevisionWorkspaceProductOnlyMapper $productOnly,
        private readonly RevisionWorkspaceServiceStoreStockMapper $serviceStoreStock,
        private readonly RevisionWorkspaceServiceExternalMapper $serviceExternal,
    ) {
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function mapMany(NoteRevision $revision): array
    {
        $items = [];

        foreach ($revision->lines() as $line) {
            $items[] = $this->mapLine($line);
        }

        return $items;
    }

    /**
     * @return array<string, mixed>
     */
    private function mapLine(NoteRevisionLineSnapshot $line): array
    {
        return match ($line->transactionType()) {
            'service_only' => $this->serviceOnly->map($line),
            'store_stock_sale_only' => $this->productOnly->map($line),
            'service_with_store_stock_part' => $this->serviceStoreStock->map($line),
            'service_with_external_purchase' => $this->serviceExternal->map($line),
            default => throw new DomainException('Tipe line revision belum didukung untuk preload workspace edit.'),
        };
    }
}
