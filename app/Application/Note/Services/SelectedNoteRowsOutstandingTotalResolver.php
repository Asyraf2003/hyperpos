<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Application\Shared\DTO\Result;

final class SelectedNoteRowsOutstandingTotalResolver
{
    public function __construct(
        private readonly SelectedNoteRowsPaymentSelectionExpander $selectionExpander,
        private readonly SelectedNoteRowsSettledTargetDetector $settledTargets,
    ) {
    }

    /**
     * @param list<array<string, mixed>> $billingRows
     * @param list<string> $selectedRowIds
     */
    public function resolve(array $billingRows, array $selectedRowIds): Result
    {
        $selectedIds = array_values(array_unique(array_filter(
            $selectedRowIds,
            static fn (string $id): bool => trim($id) !== ''
        )));

        if ($selectedIds === []) {
            return Result::success($this->sumAllOutstanding($billingRows));
        }

        return $this->sumSelectedOutstanding($billingRows, $selectedIds);
    }

    /** @param list<array<string, mixed>> $billingRows */
    private function sumAllOutstanding(array $billingRows): int
    {
        return array_reduce(
            array_filter($billingRows, static fn (array $row): bool => (int) ($row['outstanding_rupiah'] ?? 0) > 0),
            static fn (int $sum, array $row): int => $sum + (int) ($row['outstanding_rupiah'] ?? 0),
            0,
        );
    }

    /**
     * @param list<array<string, mixed>> $billingRows
     * @param list<string> $selectedIds
     */
    private function sumSelectedOutstanding(array $billingRows, array $selectedIds): Result
    {
        $billingRowsById = $this->selectionExpander->indexById($billingRows);
        $originalSelectedIds = $selectedIds;
        $selectedIds = $this->selectionExpander->expand($billingRows, $selectedIds);

        if ($selectedIds === [] && $this->settledTargets->matchesOnlySettledRows($billingRows, $originalSelectedIds)) {
            return Result::failure('Hanya billing row outstanding yang boleh dipilih untuk pembayaran.', ['payment' => ['INVALID_SELECTED_ROWS']]);
        }

        $matchedIds = [];
        $total = 0;

        foreach ($selectedIds as $selectedId) {
            $row = $billingRowsById[$selectedId] ?? null;

            if ($row === null) {
                return Result::failure('Billing row yang dipilih tidak valid untuk nota ini.', ['payment' => ['INVALID_SELECTED_ROWS']]);
            }

            $outstanding = (int) ($row['outstanding_rupiah'] ?? 0);
            if ($outstanding <= 0) {
                return Result::failure('Hanya billing row outstanding yang boleh dipilih untuk pembayaran.', ['payment' => ['INVALID_SELECTED_ROWS']]);
            }

            $matchedIds[] = $selectedId;
            $total += $outstanding;
        }

        return array_values(array_diff($selectedIds, $matchedIds)) === []
            ? Result::success($total)
            : Result::failure('Billing row yang dipilih tidak valid untuk nota ini.', ['payment' => ['INVALID_SELECTED_ROWS']]);
    }
}
