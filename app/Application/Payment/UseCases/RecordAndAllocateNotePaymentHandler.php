<?php

declare(strict_types=1);

namespace App\Application\Payment\UseCases;

use App\Application\Note\Services\AutoCloseNoteWhenFullyPaid;
use App\Application\Payment\Services\AllocatePaymentAcrossComponents;
use App\Application\Payment\Services\AllocatePaymentErrorClassifier;
use App\Application\Payment\Services\PaymentDateParser;
use App\Application\Payment\Services\ResolveNotePayableComponents;
use App\Application\Shared\DTO\Result;
use App\Core\Payment\CustomerPayment\CustomerPayment;
use App\Core\Payment\Policies\PaymentAllocationPolicy;
use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;
use App\Ports\Out\AuditLogPort;
use App\Ports\Out\Note\NoteReaderPort;
use App\Ports\Out\Payment\CustomerPaymentWriterPort;
use App\Ports\Out\Payment\PaymentComponentAllocationReaderPort;
use App\Ports\Out\Payment\PaymentComponentAllocationWriterPort;
use App\Ports\Out\TransactionManagerPort;
use App\Ports\Out\UuidPort;
use Throwable;

final class RecordAndAllocateNotePaymentHandler
{
    public function __construct(
        private readonly CustomerPaymentWriterPort $payments,
        private readonly PaymentComponentAllocationReaderPort $allocations,
        private readonly PaymentComponentAllocationWriterPort $allocationWriter,
        private readonly NoteReaderPort $notes,
        private readonly PaymentAllocationPolicy $policy,
        private readonly ResolveNotePayableComponents $components,
        private readonly AllocatePaymentAcrossComponents $allocator,
        private readonly AutoCloseNoteWhenFullyPaid $autoClose,
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

            $note = $this->notes->getById(trim($noteId))
                ?? throw new DomainException('Target payment allocation tidak ditemukan.');

            $amount = Money::fromInt($amountRupiah);
            $payment = CustomerPayment::create(
                $this->uuid->generate(),
                $amount,
                PaymentDateParser::parseYmd($paidAt, 'Paid at pada customer payment wajib berupa tanggal yang valid dengan format Y-m-d.'),
            );

            $this->policy->assertAllocatable(
                $amount,
                $payment->amountRupiah(),
                Money::zero(),
                $note->totalRupiah(),
                $this->allocations->getTotalAllocatedAmountByNoteId($note->id()),
            );

            $allocations = $this->allocator->allocate($payment->id(), $note->id(), $amount, $this->components->fromNote($note));

            $this->payments->create($payment);
            $this->allocationWriter->createMany($allocations);
            $this->autoClose->closeIfEligible($note, $payment->id());

            $this->audit->record('payment_allocated', [
                'payment_id' => $payment->id(),
                'note_id' => $note->id(),
                'amount' => $amountRupiah,
                'allocation_count' => count($allocations),
            ]);

            $this->transactions->commit();

            return Result::success([
                'payment_id' => $payment->id(),
                'allocation_count' => count($allocations),
            ], 'Pembayaran berhasil dicatat.');
        } catch (DomainException $e) {
            if ($started) $this->transactions->rollBack();
            return $this->errors->classify($e);
        } catch (Throwable $e) {
            if ($started) $this->transactions->rollBack();
            throw $e;
        }
    }
}
