<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Application\Shared\DTO\Result;
use App\Ports\Out\Note\NoteReaderPort;

final class SelectedNoteRowsPaymentAmountResolver
{
    public function __construct(
        private readonly NoteReaderPort $notes,
        private readonly NoteBillingProjectionBuilder $billingProjection,
        private readonly SelectedNoteRowsPaymentSelectionExpander $selectionExpander,
        private readonly NoteWorkspacePanelDataBuilder $workspacePanel,
    ) {
    }

    /**
     * @param list<string> $selectedRowIds
     */
    public function resolve(string $noteId, array $selectedRowIds, int $requestedAmountRupiah): Result
    {
        $note = $this->notes->getById(trim($noteId));

        if ($note === null) {
            return Result::failure('Nota tidak ditemukan.', ['payment' => ['PAYMENT_INVALID_TARGET']]);
        }

        $workspacePanel = $this->workspacePanel->build($note->id());
        $billingRows = $workspacePanel === null
            ? ($this->billingProjection->build($note->id()) ?? [])
            : $this->billingProjection->buildFromWorkspaceRows(
                (array) ($workspacePanel['rows'] ?? [])
            );
        $selectedIds = array_values(array_unique(array_filter(
            $selectedRowIds,
            static fn (string $id): bool => trim($id) !== ''
        )));

        if ($selectedIds === []) {
            $selectedOutstandingTotal = array_reduce(
                array_filter($billingRows, static fn (array $row): bool => (int) ($row['outstanding_rupiah'] ?? 0) > 0),
                static fn (int $sum, array $row): int => $sum + (int) ($row['outstanding_rupiah'] ?? 0),
                0,
            );
        } else {
            $billingRowsById = $this->selectionExpander->indexById($billingRows);
            $selectedIds = $this->selectionExpander->expand($billingRows, $selectedIds);

            $matchedIds = [];
            $selectedOutstandingTotal = 0;

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
                $selectedOutstandingTotal += $outstanding;
            }

            if (array_values(array_diff($selectedIds, $matchedIds)) !== []) {
                return Result::failure('Billing row yang dipilih tidak valid untuk nota ini.', ['payment' => ['INVALID_SELECTED_ROWS']]);
            }
        }

        if ($selectedOutstandingTotal <= 0) {
            return Result::failure('Total outstanding billing row terpilih harus lebih besar dari 0.', ['payment' => ['INVALID_SELECTED_ROWS']]);
        }

        $effectiveAmountRupiah = $requestedAmountRupiah > 0
            ? $requestedAmountRupiah
            : $selectedOutstandingTotal;

        if ($effectiveAmountRupiah > $selectedOutstandingTotal) {
            return Result::failure(
                'Nominal pembayaran melebihi total outstanding billing row yang dipilih.',
                ['payment' => ['INVALID_PAYMENT_AMOUNT']]
            );
        }

        return Result::success([
            'amount_rupiah' => $effectiveAmountRupiah,
            'selected_outstanding_total_rupiah' => $selectedOutstandingTotal,
        ]);
    }
}
