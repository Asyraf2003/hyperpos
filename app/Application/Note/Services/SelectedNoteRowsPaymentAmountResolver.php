<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Application\Shared\DTO\Result;
use App\Ports\Out\Note\NoteReaderPort;

final class SelectedNoteRowsPaymentAmountResolver
{
    public function __construct(
        private readonly NoteReaderPort $notes,
    ) {
    }

    /**
     * @param list<string> $selectedRowIds
     */
    public function resolve(string $noteId, array $selectedRowIds): Result
    {
        $note = $this->notes->getById(trim($noteId));

        if ($note === null) {
            return Result::failure('Nota tidak ditemukan.', ['payment' => ['PAYMENT_INVALID_TARGET']]);
        }

        $selectedIds = array_values(array_unique(array_filter($selectedRowIds, fn (string $id): bool => trim($id) !== '')));

        if ($selectedIds === []) {
            return Result::failure('Pilih minimal satu baris nota untuk dibayar.', ['payment' => ['INVALID_SELECTED_ROWS']]);
        }

        $matchedIds = [];
        $amount = 0;

        foreach ($note->workItems() as $item) {
            if (! in_array($item->id(), $selectedIds, true)) {
                continue;
            }

            $matchedIds[] = $item->id();
            $amount += $item->subtotalRupiah()->amount();
        }

        if (array_values(array_diff($selectedIds, $matchedIds)) !== []) {
            return Result::failure('Baris nota yang dipilih tidak valid.', ['payment' => ['INVALID_SELECTED_ROWS']]);
        }

        if ($amount <= 0) {
            return Result::failure('Total pembayaran harus lebih besar dari 0.', ['payment' => ['INVALID_SELECTED_ROWS']]);
        }

        return Result::success(['amount_rupiah' => $amount]);
    }
}
