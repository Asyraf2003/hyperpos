<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Core\Note\Note\Note;
use App\Core\Shared\Exceptions\DomainException;

final class CreateTransactionWorkspaceInlinePaymentAmountResolver
{
    /**
     * @param array<string, mixed> $payment
     */
    public function resolve(Note $note, array $payment): int
    {
        $decision = (string) ($payment['decision'] ?? 'skip');

        return match ($decision) {
            'pay_full' => $note->totalRupiah()->amount(),
            'pay_partial' => $this->resolvePartial($note, $payment),
            default => throw new DomainException('Keputusan pembayaran workspace tidak valid.'),
        };
    }

    /**
     * @param array<string, mixed> $payment
     */
    private function resolvePartial(Note $note, array $payment): int
    {
        $amount = (int) ($payment['amount_paid_rupiah'] ?? 0);

        if ($amount <= 0) {
            throw new DomainException('Nominal pembayaran sebagian wajib lebih dari 0.');
        }

        if ($amount >= $note->totalRupiah()->amount()) {
            throw new DomainException('Nominal pembayaran sebagian harus lebih kecil dari grand total nota.');
        }

        return $amount;
    }
}
