<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Core\Note\Note\Note;
use App\Core\Payment\CustomerPayment\CustomerPayment;

final class CreateTransactionWorkspaceInlinePaymentAuditPayloadBuilder
{
    /**
     * @param array<string, mixed> $payment
     * @param list<object> $allocations
     * @return array<string, mixed>
     */
    public function build(Note $note, CustomerPayment $customerPayment, array $payment, array $allocations): array
    {
        $isCash = $payment['method'] === CustomerPayment::METHOD_CASH;

        return [
            'payment_id' => $customerPayment->id(),
            'note_id' => $note->id(),
            'amount' => $payment['amount_paid_rupiah'],
            'payment_method' => $customerPayment->paymentMethod(),
            'amount_received' => $isCash ? $payment['amount_received_rupiah'] : null,
            'change' => $isCash ? $payment['change_rupiah'] : null,
            'allocation_count' => count($allocations),
            'source' => 'transaction_workspace',
            'decision' => $payment['decision'],
        ];
    }
}
