<?php

declare(strict_types=1);

namespace App\Application\Payment\Services;

use App\Application\Payment\DTO\PayableNoteComponent;
use App\Core\Note\Note\Note;
use App\Core\Shared\Exceptions\DomainException;

final class ResolveNotePayableComponents
{
    /**
     * @return list<PayableNoteComponent>
     */
    public function fromNote(Note $note): array
    {
        $components = [];
        $nextOrder = 1;

        foreach ($note->workItems() as $item) {
            $resolved = PayableComponentsFromWorkItem::resolve($item, $nextOrder);
            $components = [...$components, ...$resolved];
            $nextOrder += count($resolved);
        }

        return $components;
    }

    /**
     * @param list<string> $selectedRowIds
     * @return list<PayableNoteComponent>
     */
    public function fromSelectedRows(Note $note, array $selectedRowIds): array
    {
        $selectedIds = $this->normalizeSelectedRowIds($selectedRowIds);

        if ($selectedIds === []) {
            throw new DomainException('Pilih minimal satu line open untuk pembayaran.');
        }

        $components = [];
        $nextOrder = 1;
        $matchedIds = [];

        foreach ($note->workItems() as $item) {
            if (! in_array($item->id(), $selectedIds, true)) {
                continue;
            }

            $matchedIds[] = $item->id();

            $resolved = PayableComponentsFromWorkItem::resolve($item, $nextOrder);
            $components = [...$components, ...$resolved];
            $nextOrder += count($resolved);
        }

        if (array_values(array_diff($selectedIds, $matchedIds)) !== []) {
            throw new DomainException('Line pembayaran yang dipilih tidak valid untuk nota ini.');
        }

        if ($components === []) {
            throw new DomainException('Line pembayaran yang dipilih tidak memiliki komponen yang bisa dibayar.');
        }

        return $components;
    }

    /**
     * @param list<string> $selectedRowIds
     * @return list<string>
     */
    private function normalizeSelectedRowIds(array $selectedRowIds): array
    {
        $normalized = [];

        foreach ($selectedRowIds as $id) {
            $trimmed = trim($id);

            if ($trimmed !== '') {
                $normalized[] = $trimmed;
            }
        }

        return array_values(array_unique($normalized));
    }
}
