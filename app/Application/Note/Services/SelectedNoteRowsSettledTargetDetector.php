<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

final class SelectedNoteRowsSettledTargetDetector
{
    /**
     * @param list<array<string, mixed>> $billingRows
     * @param list<string> $selectedIds
     */
    public function matchesOnlySettledRows(array $billingRows, array $selectedIds): bool
    {
        if ($selectedIds === []) {
            return false;
        }

        foreach ($selectedIds as $selectedId) {
            if (! $this->matchesSettledRow($billingRows, $selectedId)) {
                return false;
            }
        }

        return true;
    }

    /** @param list<array<string, mixed>> $billingRows */
    private function matchesSettledRow(array $billingRows, string $selectedId): bool
    {
        $matched = false;

        foreach ($billingRows as $row) {
            $matchesRow = (string) ($row['id'] ?? '') === $selectedId
                || (string) ($row['work_item_id'] ?? '') === $selectedId;

            if (! $matchesRow) {
                continue;
            }

            $matched = true;

            if ((int) ($row['outstanding_rupiah'] ?? 0) > 0) {
                return false;
            }
        }

        return $matched;
    }
}
