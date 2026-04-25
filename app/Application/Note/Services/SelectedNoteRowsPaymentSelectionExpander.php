<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

final class SelectedNoteRowsPaymentSelectionExpander
{
    /**
     * @param list<array<string, mixed>> $billingRows
     * @param list<string> $selectedIds
     * @return list<string>
     */
    public function expand(array $billingRows, array $selectedIds): array
    {
        $billingRowsById = $this->indexById($billingRows);
        $expandedIds = [];

        foreach ($selectedIds as $selectedId) {
            if (isset($billingRowsById[$selectedId])) {
                $expandedIds[] = $selectedId;
                continue;
            }

            foreach ($billingRows as $row) {
                if ((string) ($row['work_item_id'] ?? '') === $selectedId
                    && (int) ($row['outstanding_rupiah'] ?? 0) > 0) {
                    $expandedIds[] = (string) ($row['id'] ?? '');
                }
            }
        }

        return array_values(array_unique(array_filter($expandedIds)));
    }

    /**
     * @param list<array<string, mixed>> $billingRows
     * @return array<string, array<string, mixed>>
     */
    public function indexById(array $billingRows): array
    {
        $indexed = [];

        foreach ($billingRows as $row) {
            $indexed[(string) ($row['id'] ?? '')] = $row;
        }

        return $indexed;
    }
}
