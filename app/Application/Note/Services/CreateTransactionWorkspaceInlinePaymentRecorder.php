<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Application\Payment\Services\AllocatePaymentAcrossComponents;
use App\Application\Payment\Services\BuildCustomerPaymentCashDetail;
use App\Application\Payment\Services\PaymentDateParser;
use App\Application\Payment\Services\ResolveNotePayableComponents;
use App\Core\Note\Note\Note;
use App\Core\Payment\CustomerPayment\CustomerPayment;
use App\Core\Payment\Policies\PaymentAllocationPolicy;
use App\Core\Shared\ValueObjects\Money;
use App\Ports\Out\AuditLogPort;
use App\Ports\Out\Payment\CustomerPaymentWriterPort;
use App\Ports\Out\Payment\PaymentAllocationReaderPort;
use App\Ports\Out\Payment\RefundComponentAllocationReaderPort;
use App\Ports\Out\Payment\PaymentComponentAllocationWriterPort;
use App\Ports\Out\UuidPort;

final class CreateTransactionWorkspaceInlinePaymentRecorder
{
    public function __construct(
        private readonly CustomerPaymentWriterPort $payments,
        private readonly PaymentComponentAllocationWriterPort $componentAllocations,
        private readonly PaymentAllocationReaderPort $paymentAllocations,
        private readonly RefundComponentAllocationReaderPort $refunds,
        private readonly PaymentAllocationPolicy $policy,
        private readonly UuidPort $uuid,
        private readonly AuditLogPort $audit,
        private readonly CreateTransactionWorkspaceInlinePaymentContextResolver $context,
        private readonly ResolveNotePayableComponents $components,
        private readonly AllocatePaymentAcrossComponents $allocator,
        private readonly AutoCloseNoteWhenFullyPaid $autoClose,
        private readonly BuildCustomerPaymentCashDetail $cashDetails,
        private readonly CreateTransactionWorkspaceInlinePaymentAuditPayloadBuilder $auditPayloads,
        private readonly CreateTransactionWorkspaceInlinePaymentSummaryBuilder $summaries,
    ) {
    }

    /**
     * @param mixed $payload
     * @return array{decision:string,amount_paid_rupiah:int,change_rupiah:int}
     */
    public function record(Note $note, mixed $payload): array
    {
        $payment = $this->context->resolve($note, $payload);

        if ($payment['decision'] === 'skip') {
            return $this->summaries->skipped();
        }

        $money = Money::fromInt($payment['amount_paid_rupiah']);
        $customerPayment = CustomerPayment::create(
            $this->uuid->generate(),
            $money,
            PaymentDateParser::parseYmd($payment['paid_at'], 'Tanggal bayar wajib valid dengan format Y-m-d.'),
            $payment['method'],
        );

        $cashDetail = $this->cashDetails->execute(
            $customerPayment,
            $money,
            $payment['method'] === CustomerPayment::METHOD_CASH
                ? $payment['amount_received_rupiah']
                : null,
        );

        $grossAllocated = $this->paymentAllocations->getTotalAllocatedAmountByNoteId($note->id());
        $refunded = $this->refunds->getTotalRefundedAmountByNoteId($note->id());
        $existingAllocated = Money::fromInt(max($grossAllocated->amount() - $refunded->amount(), 0));

        $this->policy->assertAllocatable(
            $money,
            $customerPayment->amountRupiah(),
            Money::zero(),
            $note->totalRupiah(),
            $existingAllocated,
        );

        $allocations = $this->allocator->allocate(
            $customerPayment->id(),
            $note->id(),
            $money,
            $this->components->fromNote($note),
        );

        $this->payments->create($customerPayment, $cashDetail);
        $this->componentAllocations->createMany($allocations);
        $this->autoClose->closeIfEligible($note, $customerPayment->id());

        $this->audit->record(
            'payment_allocated',
            $this->auditPayloads->build($note, $customerPayment, $payment, $allocations)
        );

        return $this->summaries->paid($payment);
    }
}
