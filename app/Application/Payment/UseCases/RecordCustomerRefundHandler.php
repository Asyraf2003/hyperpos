<?php

declare(strict_types=1);

namespace App\Application\Payment\UseCases;

use App\Application\Payment\Services\AllocateRefundAcrossComponents;
use App\Application\Shared\DTO\Result;
use App\Core\Payment\CustomerRefund\CustomerRefund;
use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;
use App\Ports\Out\AuditLogPort;
use App\Ports\Out\Note\NoteReaderPort;
use App\Ports\Out\Payment\CustomerPaymentReaderPort;
use App\Ports\Out\Payment\CustomerRefundReaderPort;
use App\Ports\Out\Payment\CustomerRefundWriterPort;
use App\Ports\Out\Payment\PaymentAllocationReaderPort;
use App\Ports\Out\Payment\RefundComponentAllocationWriterPort;
use App\Ports\Out\TransactionManagerPort;
use App\Ports\Out\UuidPort;
use Throwable;

final class RecordCustomerRefundHandler
{
    use RecordCustomerRefundSupportTrait;

    public function __construct(
        private readonly CustomerPaymentReaderPort $customerPayments,
        private readonly CustomerRefundReaderPort $refunds,
        private readonly CustomerRefundWriterPort $refundWriter,
        private readonly PaymentAllocationReaderPort $allocations,
        private readonly RefundComponentAllocationWriterPort $refundAllocationWriter,
        private readonly AllocateRefundAcrossComponents $refundAllocator,
        private readonly NoteReaderPort $notes,
        private readonly TransactionManagerPort $transactions,
        private readonly UuidPort $uuid,
        private readonly AuditLogPort $audit,
    ) {
    }

    public function handle(
        string $customerPaymentId,
        string $noteId,
        int $amountRupiah,
        string $refundedAt,
        string $reason,
        string $performedByActorId,
    ): Result {
        if (trim($reason) === '') {
            return Result::failure('Alasan refund wajib diisi.', ['refund' => ['AUDIT_REASON_REQUIRED']]);
        }

        $started = false;

        try {
            if (trim($performedByActorId) === '') throw new DomainException('Actor refund wajib ada.');

            $this->transactions->begin();
            $started = true;

            $payment = $this->customerPayments->getById(trim($customerPaymentId));
            $note = $this->notes->getById(trim($noteId));
            if ($payment === null || $note === null) throw new DomainException('Target refund tidak ditemukan.');

            $amount = Money::fromInt($amountRupiah);
            $refunded = $this->parseRefundedAt($refundedAt);
            $pairAllocated = $this->allocations->getTotalAllocatedAmountByCustomerPaymentIdAndNoteId($payment->id(), $note->id());
            $pairRefunded = $this->refunds->getTotalRefundedAmountByCustomerPaymentIdAndNoteId($payment->id(), $note->id());

            if ($pairRefunded->add($amount)->greaterThan($pairAllocated)) {
                throw new DomainException('Refund melebihi total allocation untuk payment-note pair.');
            }

            $refund = CustomerRefund::create(
                $this->uuid->generate(),
                $payment->id(),
                $note->id(),
                $amount,
                $refunded,
                trim($reason),
            );

            $refundAllocations = $this->refundAllocator->allocate(
                $refund->id(),
                $payment->id(),
                $note->id(),
                $amount,
            );

            $this->refundWriter->create($refund);
            $this->refundAllocationWriter->createMany($refundAllocations);

            $this->audit->record('customer_refund_recorded', array_merge(
                $this->formatAuditPayload($refund, $performedByActorId),
                ['refund_allocation_count' => count($refundAllocations)]
            ));

            $this->transactions->commit();

            return Result::success(array_merge(
                $this->formatSuccessPayload($refund),
                ['refund_allocation_count' => count($refundAllocations)]
            ), 'Customer refund berhasil dicatat.');
        } catch (DomainException $e) {
            if ($started) $this->transactions->rollBack();
            return $this->classify($e);
        } catch (Throwable $e) {
            if ($started) $this->transactions->rollBack();
            throw $e;
        }
    }
}
