<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Application\Shared\DTO\Result;
use App\Ports\Out\Note\NoteReaderPort;

final class SelectedNoteRowsPaymentAmountResolver
{
    public function __construct(
        private readonly NoteReaderPort $notes,
        private readonly NoteOperationalRowSettlementProjector $settlements,
        private readonly WorkItemOperationalStatusResolver $statuses,
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

        $selectedIds = array_values(array_unique(array_filter(
            $selectedRowIds,
            static fn (string $id): bool => trim($id) !== ''
        )));

        if ($selectedIds === []) {
            return Result::failure('Pilih minimal satu line open untuk dibayar.', ['payment' => ['INVALID_SELECTED_ROWS']]);
        }

        if ($requestedAmountRupiah <= 0) {
            return Result::failure('Nominal pembayaran harus lebih besar dari 0.', ['payment' => ['INVALID_PAYMENT_AMOUNT']]);
        }

        $settlements = $this->settlements->build($note->id(), $note->workItems());

        $matchedIds = [];
        $selectedOutstandingTotal = 0;

        foreach ($note->workItems() as $item) {
            if (! in_array($item->id(), $selectedIds, true)) {
                continue;
            }

            $matchedIds[] = $item->id();

            $settlement = $settlements[$item->id()] ?? [
                'refunded_rupiah' => 0,
                'outstanding_rupiah' => $item->subtotalRupiah()->amount(),
            ];

            $refundedRupiah = (int) ($settlement['refunded_rupiah'] ?? 0);
            $outstandingRupiah = (int) ($settlement['outstanding_rupiah'] ?? 0);
            $status = $this->statuses->resolve($outstandingRupiah, $refundedRupiah);

            if ($status !== WorkItemOperationalStatusResolver::STATUS_OPEN) {
                return Result::failure(
                    'Hanya line Open yang boleh dipilih untuk pembayaran.',
                    ['payment' => ['INVALID_SELECTED_ROWS']]
                );
            }

            $selectedOutstandingTotal += $outstandingRupiah;
        }

        if (array_values(array_diff($selectedIds, $matchedIds)) !== []) {
            return Result::failure('Line yang dipilih tidak valid untuk nota ini.', ['payment' => ['INVALID_SELECTED_ROWS']]);
        }

        if ($selectedOutstandingTotal <= 0) {
            return Result::failure('Total outstanding line terpilih harus lebih besar dari 0.', ['payment' => ['INVALID_SELECTED_ROWS']]);
        }

        if ($requestedAmountRupiah > $selectedOutstandingTotal) {
            return Result::failure(
                'Nominal pembayaran melebihi total outstanding line yang dipilih.',
                ['payment' => ['INVALID_PAYMENT_AMOUNT']]
            );
        }

        return Result::success([
            'amount_rupiah' => $requestedAmountRupiah,
            'selected_outstanding_total_rupiah' => $selectedOutstandingTotal,
        ]);
    }
}
