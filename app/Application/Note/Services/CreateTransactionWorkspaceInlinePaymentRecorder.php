<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Core\Note\Note\Note;
use App\Core\Payment\CustomerPayment\CustomerPayment;
use App\Core\Payment\PaymentAllocation\PaymentAllocation;
use App\Core\Payment\Policies\PaymentAllocationPolicy;
use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;
use App\Ports\Out\AuditLogPort;
use App\Ports\Out\Payment\CustomerPaymentWriterPort;
use App\Ports\Out\Payment\PaymentAllocationWriterPort;
use App\Ports\Out\UuidPort;
use DateTimeImmutable;

final class CreateTransactionWorkspaceInlinePaymentRecorder
{
    public function __construct(
        private readonly CustomerPaymentWriterPort $payments,
        private readonly PaymentAllocationWriterPort $allocations,
        private readonly PaymentAllocationPolicy $policy,
        private readonly UuidPort $uuid,
        private readonly AuditLogPort $audit,
        private readonly CreateTransactionWorkspaceInlinePaymentAmountResolver $amounts,
    ) {
    }

    /**
     * @param mixed $payload
     * @return array{decision:string,amount_paid_rupiah:int,change_rupiah:int}
     */
    public function record(Note $note, mixed $payload): array
    {
        $payment = is_array($payload) ? $payload : [];
        $decision = (string) ($payment['decision'] ?? 'skip');

        if ($decision === 'skip') {
            return ['decision' => 'skip', 'amount_paid_rupiah' => 0, 'change_rupiah' => 0];
        }

        $method = (string) ($payment['payment_method'] ?? '');
        if (! in_array($method, ['cash', 'transfer'], true)) {
            throw new DomainException('Metode pembayaran workspace tidak valid.');
        }

        $amount = $this->amounts->resolve($note, $payment);
        $received = (int) ($payment['amount_received_rupiah'] ?? 0);

        if ($method === 'cash' && $received < $amount) {
            throw new DomainException('Uang masuk cash tidak boleh kurang dari total yang dibayar.');
        }

        $money = Money::fromInt($amount);
        $customerPayment = CustomerPayment::create($this->uuid->generate(), $money, $this->parsePaidAt($payment['paid_at'] ?? null));

        $this->policy->assertAllocatable($money, $customerPayment->amountRupiah(), Money::zero(), $note->totalRupiah(), Money::zero());
        $this->payments->create($customerPayment);

        $allocation = PaymentAllocation::create($this->uuid->generate(), $customerPayment->id(), $note->id(), $money);
        $this->allocations->create($allocation);

        $this->audit->record('payment_allocated', [
            'payment_id' => $customerPayment->id(),
            'note_id' => $note->id(),
            'amount' => $amount,
            'source' => 'transaction_workspace',
            'decision' => $decision,
        ]);

        return [
            'decision' => $decision,
            'amount_paid_rupiah' => $amount,
            'change_rupiah' => $method === 'cash' ? max($received - $amount, 0) : 0,
        ];
    }

    private function parsePaidAt(mixed $value): DateTimeImmutable
    {
        if (! is_string($value)) {
            throw new DomainException('Tanggal bayar wajib diisi.');
        }

        $normalized = trim($value);
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $normalized);

        if ($parsed === false || $parsed->format('Y-m-d') !== $normalized) {
            throw new DomainException('Tanggal bayar wajib valid dengan format Y-m-d.');
        }

        return $parsed;
    }
}
