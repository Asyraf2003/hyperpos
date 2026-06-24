<?php

declare(strict_types=1);

namespace App\Application\Payment\Services;

use App\Application\Payment\DTO\PayableNoteComponent;
use App\Core\Payment\PaymentComponentAllocation\PaymentComponentAllocation;
use App\Core\Payment\PaymentComponentAllocation\PaymentComponentType;
use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;
use App\Ports\Out\Inventory\InventoryMovementReaderPort;
use App\Ports\Out\Payment\PaymentComponentAllocationReaderPort;
use App\Ports\Out\Payment\RefundComponentAllocationReaderPort;
use App\Ports\Out\UuidPort;

final class AllocatePaymentAcrossComponents
{
    public function __construct(
        private readonly PaymentComponentAllocationReaderPort $existingAllocations,
        private readonly RefundComponentAllocationReaderPort $refunds,
        private readonly InventoryMovementReaderPort $inventoryMovements,
        private readonly UuidPort $uuid,
    ) {
    }

    /**
     * @param list<PayableNoteComponent> $components
     * @return list<PaymentComponentAllocation>
     */
    public function allocate(string $paymentId, string $noteId, Money $amount, array $components): array
    {
        $remaining = $amount->amount();
        $allocations = [];
        $existing = ExistingPaymentComponentTotals::build($this->existingAllocations, $noteId, $this->refunds);
        $refunded = $this->refundedComponentTotals($noteId);
        $ordered = SortPayableNoteComponents::byPriority($components);
        $priority = 1;

        foreach ($ordered as $component) {
            if ($this->isReversedRefundedStoreStockPart($component, $refunded)) {
                continue;
            }

            $key = ExistingPaymentComponentTotals::key($component->componentType(), $component->componentRefId());
            $already = $existing[$key] ?? 0;
            $available = max($component->amountRupiah()->amount() - $already, 0);

            if ($available === 0) {
                continue;
            }

            $take = min($remaining, $available);

            if ($take <= 0) {
                break;
            }

            $allocations[] = PaymentComponentAllocation::create(
                $this->uuid->generate(),
                $paymentId,
                $noteId,
                $component->workItemId(),
                $component->componentType(),
                $component->componentRefId(),
                $component->amountRupiah(),
                Money::fromInt($take),
                $priority++,
            );

            $existing[$key] = $already + $take;
            $remaining -= $take;

            if ($remaining === 0) {
                break;
            }
        }

        if ($allocations === []) {
            throw new DomainException('Tidak ada komponen note yang bisa dialokasikan untuk payment ini.');
        }

        if ($remaining > 0) {
            throw new DomainException('Payment tidak bisa dialokasikan penuh ke komponen note.');
        }

        return $allocations;
    }

    /**
     * @return array<string, int>
     */
    private function refundedComponentTotals(string $noteId): array
    {
        $totals = [];

        foreach ($this->refunds->listByNoteId($noteId) as $refund) {
            $key = ExistingPaymentComponentTotals::key($refund->componentType(), $refund->componentRefId());
            $totals[$key] = ($totals[$key] ?? 0) + $refund->refundedAmountRupiah()->amount();
        }

        return $totals;
    }

    /**
     * @param array<string, int> $refunded
     */
    private function isReversedRefundedStoreStockPart(PayableNoteComponent $component, array $refunded): bool
    {
        if ($component->componentType() !== PaymentComponentType::SERVICE_STORE_STOCK_PART) {
            return false;
        }

        $key = ExistingPaymentComponentTotals::key($component->componentType(), $component->componentRefId());

        if (($refunded[$key] ?? 0) <= 0) {
            return false;
        }

        return $this->inventoryMovements->getBySource(
            'work_item_store_stock_line_reversal',
            $component->componentRefId(),
        ) !== [];
    }
}
