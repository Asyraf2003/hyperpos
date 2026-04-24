<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Core\Note\Note\Note;
use App\Core\Shared\Exceptions\DomainException;

final class SelectedActiveWorkItemsResolver
{
    /**
     * @param list<string> $selectedRowIds
     * @return array{
     *   selected_row_ids: list<string>,
     *   selected_items: array<int, \App\Core\Note\WorkItem\WorkItem>,
     *   remaining_items: array<int, \App\Core\Note\WorkItem\WorkItem>
     * }
     */
    public function resolve(Note $note, array $selectedRowIds): array
    {
        $normalizedIds = array_values(array_unique(array_filter(
            $selectedRowIds,
            static fn (string $id): bool => trim($id) !== '',
        )));

        if ($normalizedIds === []) {
            throw new DomainException('Minimal satu line wajib dipilih.');
        }

        $itemsById = [];
        foreach ($note->workItems() as $item) {
            $itemsById[$item->id()] = $item;
        }

        foreach ($normalizedIds as $rowId) {
            if (!isset($itemsById[$rowId])) {
                throw new DomainException('Line yang dipilih tidak ditemukan pada note aktif.');
            }
        }

        $selectedItems = [];
        $remainingItems = [];

        foreach ($note->workItems() as $item) {
            if (in_array($item->id(), $normalizedIds, true)) {
                $selectedItems[] = $item;
                continue;
            }

            $remainingItems[] = $item;
        }

        return [
            'selected_row_ids' => $normalizedIds,
            'selected_items' => $selectedItems,
            'remaining_items' => $remainingItems,
        ];
    }
}
