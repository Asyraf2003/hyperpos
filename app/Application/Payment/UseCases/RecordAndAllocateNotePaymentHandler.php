<?php

declare(strict_types=1);

namespace App\Application\Payment\UseCases;

use App\Application\Payment\Services\AllocatePaymentErrorClassifier;
use App\Application\Shared\DTO\Result;
use App\Core\Payment\CustomerPayment\CustomerPayment;
use App\Core\Payment\PaymentAllocation\PaymentAllocation;
use App\Core\Payment\Policies\PaymentAllocationPolicy;
use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;
use App\Ports\Out\AuditLogPort;
use App\Ports\Out\Note\NoteReaderPort;
use App\Ports\Out\Payment\CustomerPaymentWriterPort;
use App\Ports\Out\Payment\PaymentAllocationReaderPort;
use App\Ports\Out\Payment\PaymentAllocationWriterPort;
use App\Ports\Out\TransactionManagerPort;
use App\Ports\Out\UuidPort;
use DateTimeImmutable;
use Throwable;

final class RecordAndAllocateNotePaymentHandler
{
    public function __construct(
        private readonly CustomerPaymentWriterPort $payments,
        private readonly PaymentAllocationReaderPort $allocations,
        private readonly PaymentAllocationWriterPort $allocationWriter,
        private readonly NoteReaderPort $notes,
        private readonly PaymentAllocationPolicy $policy,
        private readonly TransactionManagerPort $transactions,
        private readonly UuidPort $uuid,
        private readonly AllocatePaymentErrorClassifier $errors,
        private readonly AuditLogPort $audit,
    ) {
    }

    public function handle(string $noteId, int $amountRupiah, string $paidAt): Result
    {
        $started = false;

        try {
            $this->transactions->begin();
            $started = true;

            $note = $this->notes->getById(trim($noteId)) ?? throw new DomainException('Target payment allocation tidak ditemukan.');
            $amount = Money::fromInt($amountRupiah);
            $payment = CustomerPayment::create($this->uuid->generate(), $amount, $this->parsePaidAt($paidAt));

            $this->policy->assertAllocatable(
                $amount,
                $payment->amountRupiah(),
                Money::zero(),
                $note->totalRupiah(),
                $this->allocations->getTotalAllocatedAmountByNoteId($note->id())
            );

            $this->payments->create($payment);

            $allocation = PaymentAllocation::create($this->uuid->generate(), $payment->id(), $note->id(), $amount);
            $this->allocationWriter->create($allocation);

            $this->audit->record('payment_allocated', ['payment_id' => $payment->id(), 'note_id' => $note->id(), 'amount' => $amountRupiah]);
            $this->transactions->commit();

            return Result::success(['payment_id' => $payment->id(), 'allocation_id' => $allocation->id()], 'Pembayaran berhasil dicatat.');
        } catch (DomainException $e) {
            if ($started) {
                $this->transactions->rollBack();
            }

            return $this->errors->classify($e);
        } catch (Throwable $e) {
            if ($started) {
                $this->transactions->rollBack();
            }

            throw $e;
        }
    }

    private function parsePaidAt(string $paidAt): DateTimeImmutable
    {
        $normalized = trim($paidAt);
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $normalized);

        if ($parsed === false || $parsed->format('Y-m-d') !== $normalized) {
            throw new DomainException('Paid at pada customer payment wajib berupa tanggal yang valid dengan format Y-m-d.');
        }

        return $parsed;
    }
}
