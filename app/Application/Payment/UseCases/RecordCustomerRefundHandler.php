<?php

declare(strict_types=1);

namespace App\Application\Payment\UseCases;

use App\Application\Shared\DTO\Result;
use App\Core\Payment\CustomerRefund\CustomerRefund;
use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;
use App\Ports\Out\AuditLogPort;
use App\Ports\Out\Note\NoteReaderPort;
use App\Ports\Out\Payment\{CustomerPaymentReaderPort, CustomerRefundReaderPort, CustomerRefundWriterPort, PaymentAllocationReaderPort};
use App\Ports\Out\TransactionManagerPort;
use App\Ports\Out\UuidPort;
use DateTimeImmutable;
use Throwable;

final class RecordCustomerRefundHandler
{
    public function __construct(
        private readonly CustomerPaymentReaderPort $customerPayments,
        private readonly CustomerRefundReaderPort $refunds,
        private readonly CustomerRefundWriterPort $refundWriter,
        private readonly PaymentAllocationReaderPort $allocations,
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

            $this->refundWriter->create($refund);

            $this->audit->record('customer_refund_recorded', [
                'refund_id' => $refund->id(),
                'customer_payment_id' => $refund->customerPaymentId(),
                'note_id' => $refund->noteId(),
                'amount_rupiah' => $refund->amountRupiah()->amount(),
                'refunded_at' => $refund->refundedAt()->format('Y-m-d'),
                'reason' => $refund->reason(),
                'performed_by_actor_id' => trim($performedByActorId),
            ]);

            $this->transactions->commit();

            return Result::success([
                'refund' => [
                    'id' => $refund->id(),
                    'customer_payment_id' => $refund->customerPaymentId(),
                    'note_id' => $refund->noteId(),
                    'amount_rupiah' => $refund->amountRupiah()->amount(),
                    'refunded_at' => $refund->refundedAt()->format('Y-m-d'),
                    'reason' => $refund->reason(),
                ],
            ], 'Customer refund berhasil dicatat.');
        } catch (DomainException $e) {
            if ($started) $this->transactions->rollBack();
            return $this->classify($e);
        } catch (Throwable $e) {
            if ($started) $this->transactions->rollBack();
            throw $e;
        }
    }

    private function parseRefundedAt(string $refundedAt): DateTimeImmutable
    {
        $normalized = trim($refundedAt);
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $normalized);

        if ($parsed === false || $parsed->format('Y-m-d') !== $normalized) {
            throw new DomainException('Refunded at pada customer refund wajib berupa tanggal yang valid dengan format Y-m-d.');
        }

        return $parsed;
    }

    private function classify(DomainException $e): Result
    {
        $message = $e->getMessage();
        $code = match ($message) {
            'Target refund tidak ditemukan.' => 'REFUND_INVALID_TARGET',
            'Refund melebihi total allocation untuk payment-note pair.' => 'REFUND_EXCEEDS_ALLOCATED_PAIR',
            default => 'INVALID_CUSTOMER_REFUND',
        };

        return Result::failure($message, ['refund' => [$code]]);
    }
}
