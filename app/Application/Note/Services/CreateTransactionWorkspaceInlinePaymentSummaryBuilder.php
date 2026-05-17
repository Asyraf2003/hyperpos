<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

final class CreateTransactionWorkspaceInlinePaymentSummaryBuilder
{
    /**
     * @return array{decision:string,amount_paid_rupiah:int,change_rupiah:int}
     */
    public function skipped(): array
    {
        return [
            'decision' => 'skip',
            'amount_paid_rupiah' => 0,
            'change_rupiah' => 0,
        ];
    }

    /**
     * @param array<string, mixed> $payment
     * @return array{decision:string,amount_paid_rupiah:int,change_rupiah:int}
     */
    public function paid(array $payment): array
    {
        return [
            'decision' => (string) $payment['decision'],
            'amount_paid_rupiah' => (int) $payment['amount_paid_rupiah'],
            'change_rupiah' => (int) $payment['change_rupiah'],
        ];
    }
}
