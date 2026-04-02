<?php

declare(strict_types=1);

namespace App\Application\Payment\Services;

use App\Core\Payment\RefundComponentAllocation\RefundComponentAllocation;
use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;
use App\Ports\Out\Payment\PaymentComponentAllocationReaderPort;
use App\Ports\Out\Payment\RefundComponentAllocationReaderPort;
use App\Ports\Out\UuidPort;

final class AllocateRefundAcrossComponents
{
    public function __construct(
        private readonly PaymentComponentAllocationReaderPort $payments,
        private readonly RefundComponentAllocationReaderPort $refunds,
        private readonly UuidPort $uuid,
    ) {
    }

    /**
     * @return list<RefundComponentAllocation>
     */
    public function allocate(
        string $customerRefundId,
        string $customerPaymentId,
        string $noteId,
        Money $amount,
    ): array {
        $remaining = $amount->amount();
        $allocations = [];
        $paymentAllocations = $this->paymentAllocations($customerPaymentId, $noteId);
        $alreadyRefunded = $this->alreadyRefunded($customerPaymentId, $noteId);
        $priority = 1;

        foreach ($paymentAllocations as $paymentAllocation) {
            $key = $this->key($paymentAllocation->componentType(), $paymentAllocation->componentRefId());
            $allocated = $paymentAllocation->allocatedAmountRupiah()->amount();
            $refunded = $alreadyRefunded[$key] ?? 0;
            $available = max($allocated - $refunded, 0);

            if ($available === 0) {
                continue;
            }

            $take = min($remaining, $available);

            if ($take <= 0) {
                break;
            }

            $allocations[] = RefundComponentAllocation::create(
                $this->uuid->generate(),
                $customerRefundId,
                $customerPaymentId,
                $noteId,
                $paymentAllocation->workItemId(),
                $paymentAllocation->componentType(),
                $paymentAllocation->componentRefId(),
                Money::fromInt($take),
                $priority++,
            );

            $alreadyRefunded[$key] = $refunded + $take;
            $remaining -= $take;

            if ($remaining === 0) {
                break;
            }
        }

        if ($allocations === []) {
            throw new DomainException('Tidak ada komponen payment yang bisa direfund.');
        }

        if ($remaining > 0) {
            throw new DomainException('Refund tidak bisa dialokasikan penuh ke komponen payment.');
        }

        return $allocations;
    }

    private function key(string $componentType, string $componentRefId): string
    {
        return $componentType . '|' . $componentRefId;
    }

    /**
     * @return array<string, int>
     */
    private function alreadyRefunded(string $customerPaymentId, string $noteId): array
    {
        $totals = [];

        foreach ($this->refunds->listByNoteId($noteId) as $allocation) {
            if ($allocation->customerPaymentId() !== $customerPaymentId) {
                continue;
            }

            $key = $this->key($allocation->componentType(), $allocation->componentRefId());
            $totals[$key] = ($totals[$key] ?? 0) + $allocation->refundedAmountRupiah()->amount();
        }

        return $totals;
    }

    private function paymentAllocations(string $customerPaymentId, string $noteId): array
    {
        $allocations = array_filter(
            $this->payments->listByNoteId($noteId),
            static fn ($allocation): bool => $allocation->customerPaymentId() === $customerPaymentId
        );

        usort($allocations, static function ($left, $right): int {
            return $right->allocationPriority() <=> $left->allocationPriority();
        });

        return array_values($allocations);
    }
}
