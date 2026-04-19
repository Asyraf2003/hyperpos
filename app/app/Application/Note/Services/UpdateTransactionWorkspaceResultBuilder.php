<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Application\Shared\DTO\Result;
use App\Core\Note\Note\Note;

final class UpdateTransactionWorkspaceResultBuilder
{
    /**
     * @param array{decision:string,amount_paid_rupiah:int,change_rupiah:int} $paymentSummary
     */
    public function success(Note $note, array $paymentSummary): Result
    {
        return Result::success(
            [
                'note' => [
                    'id' => $note->id(),
                    'customer_name' => $note->customerName(),
                    'transaction_date' => $note->transactionDate()->format('Y-m-d'),
                    'total_rupiah' => $note->totalRupiah()->amount(),
                ],
                'inline_payment' => $paymentSummary,
            ],
            $this->successMessage($paymentSummary)
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function auditPayload(Note $note, int $itemsCount, array $paymentSummary): array
    {
        return [
            'note_id' => $note->id(),
            'customer_name' => $note->customerName(),
            'transaction_date' => $note->transactionDate()->format('Y-m-d'),
            'items_count' => $itemsCount,
            'total_rupiah' => $note->totalRupiah()->amount(),
            'payment_decision' => $paymentSummary['decision'],
            'amount_paid_rupiah' => $paymentSummary['amount_paid_rupiah'],
        ];
    }

    /**
     * @param array{decision:string,amount_paid_rupiah:int,change_rupiah:int} $paymentSummary
     */
    private function successMessage(array $paymentSummary): string
    {
        if ($paymentSummary['decision'] === 'skip') {
            return 'Perubahan workspace nota berhasil disimpan.';
        }

        if ($paymentSummary['change_rupiah'] > 0) {
            return 'Perubahan workspace nota dan pembayaran berhasil dicatat. Kembalian: '
                . number_format($paymentSummary['change_rupiah'], 0, ',', '.');
        }

        return 'Perubahan workspace nota dan pembayaran berhasil dicatat.';
    }
}
