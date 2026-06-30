<?php

declare(strict_types=1);

namespace App\Application\Payment\Services;

use App\Application\Payment\DTO\RecordedNotePayment;
use App\Core\Payment\CustomerPayment\CustomerPayment;

final class RecordAndAllocateNotePaymentAuditPayloadBuilder
{
    /**
     * @param list<string> $selectedRowIds
     * @return array<string, mixed>
     */
    public function build(
        RecordedNotePayment $recorded,
        string $noteId,
        int $amountRupiah,
        ?int $amountReceivedRupiah,
        array $selectedRowIds,
    ): array {
        $paymentMethod = $recorded->payment()->paymentMethod();

        return [
            'payment_id' => $recorded->payment()->id(),
            'note_id' => trim($noteId),
            'amount' => $amountRupiah,
            'payment_method' => $paymentMethod,
            'amount_received' => $amountReceivedRupiah,
            'change' => $this->changeAmount($paymentMethod, $amountRupiah, $amountReceivedRupiah),
            'allocation_count' => $recorded->allocationCount(),
            'selected_row_ids' => $selectedRowIds,
        ];
    }

    private function changeAmount(string $paymentMethod, int $amountRupiah, ?int $amountReceivedRupiah): ?int
    {
        if ($paymentMethod !== CustomerPayment::METHOD_CASH || $amountReceivedRupiah === null) {
            return null;
        }

        return max($amountReceivedRupiah - $amountRupiah, 0);
    }
}
