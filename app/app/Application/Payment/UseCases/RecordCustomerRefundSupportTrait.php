<?php

declare(strict_types=1);

namespace App\Application\Payment\UseCases;

use App\Application\Shared\DTO\Result;
use App\Core\Payment\CustomerRefund\CustomerRefund;
use App\Core\Shared\Exceptions\DomainException;
use DateTimeImmutable;

trait RecordCustomerRefundSupportTrait
{
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

    /**
     * @return array<string, mixed>
     */
    private function formatSuccessPayload(CustomerRefund $refund): array
    {
        return [
            'refund' => [
                'id' => $refund->id(),
                'customer_payment_id' => $refund->customerPaymentId(),
                'note_id' => $refund->noteId(),
                'amount_rupiah' => $refund->amountRupiah()->amount(),
                'refunded_at' => $refund->refundedAt()->format('Y-m-d'),
                'reason' => $refund->reason(),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formatAuditPayload(CustomerRefund $refund, string $actorId): array
    {
        return [
            'refund_id' => $refund->id(),
            'customer_payment_id' => $refund->customerPaymentId(),
            'note_id' => $refund->noteId(),
            'amount_rupiah' => $refund->amountRupiah()->amount(),
            'refunded_at' => $refund->refundedAt()->format('Y-m-d'),
            'reason' => $refund->reason(),
            'performed_by_actor_id' => trim($actorId),
        ];
    }
}
