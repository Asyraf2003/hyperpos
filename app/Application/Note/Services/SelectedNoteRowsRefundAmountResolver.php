<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Application\Shared\DTO\Result;
use App\Ports\Out\Note\NoteReaderPort;

final class SelectedNoteRowsRefundAmountResolver
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
            return Result::failure('Nota tidak ditemukan.', ['refund' => ['REFUND_INVALID_TARGET']]);
        }

        $selectedIds = array_values(array_unique(array_filter(
            $selectedRowIds,
            static fn (string $id): bool => trim($id) !== ''
        )));

        if ($requestedAmountRupiah <= 0) {
            return Result::failure('Nominal refund harus lebih besar dari 0.', ['refund' => ['INVALID_REFUND_AMOUNT']]);
        }

        $restrictToSelection = $selectedIds !== [];
        $settlements = $this->settlements->build($note->id(), $note->workItems());

        $matchedIds = [];
        $selectedRefundableTotal = 0;

        foreach ($note->workItems() as $item) {
            $settlement = $settlements[$item->id()] ?? [
                'refunded_rupiah' => 0,
                'net_paid_rupiah' => 0,
                'outstanding_rupiah' => $item->subtotalRupiah()->amount(),
            ];

            $refundedRupiah = (int) ($settlement['refunded_rupiah'] ?? 0);
            $netPaidRupiah = (int) ($settlement['net_paid_rupiah'] ?? 0);
            $outstandingRupiah = (int) ($settlement['outstanding_rupiah'] ?? 0);
            $status = $this->statuses->resolve($outstandingRupiah, $refundedRupiah);

            if ($restrictToSelection) {
                if (! in_array($item->id(), $selectedIds, true)) {
                    continue;
                }

                $matchedIds[] = $item->id();

                if ($status !== WorkItemOperationalStatusResolver::STATUS_CLOSE) {
                    return Result::failure(
                        'Hanya line Close yang boleh dipilih untuk refund.',
                        ['refund' => ['INVALID_SELECTED_ROWS']]
                    );
                }

                $selectedRefundableTotal += $netPaidRupiah;
                continue;
            }

            if ($status === WorkItemOperationalStatusResolver::STATUS_CLOSE) {
                $selectedRefundableTotal += $netPaidRupiah;
            }
        }

        if ($restrictToSelection && array_values(array_diff($selectedIds, $matchedIds)) !== []) {
            return Result::failure('Line refund yang dipilih tidak valid untuk nota ini.', ['refund' => ['INVALID_SELECTED_ROWS']]);
        }

        if ($selectedRefundableTotal <= 0) {
            return Result::failure('Total refundable line terpilih harus lebih besar dari 0.', ['refund' => ['INVALID_SELECTED_ROWS']]);
        }

        if ($requestedAmountRupiah > $selectedRefundableTotal) {
            return Result::failure(
                'Nominal refund melebihi total refundable line yang dipilih.',
                ['refund' => ['INVALID_REFUND_AMOUNT']]
            );
        }

        return Result::success([
            'amount_rupiah' => $requestedAmountRupiah,
            'selected_refundable_total_rupiah' => $selectedRefundableTotal,
        ]);
    }
}
