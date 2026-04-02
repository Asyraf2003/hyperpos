<?php

declare(strict_types=1);

namespace App\Application\Payment\Services;

use App\Application\Payment\DTO\PayableNoteComponent;
use App\Core\Payment\PaymentComponentAllocation\PaymentComponentAllocation;
use App\Core\Payment\PaymentComponentAllocation\PaymentComponentType;
use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;
use App\Ports\Out\Payment\PaymentComponentAllocationReaderPort;
use App\Ports\Out\UuidPort;

final class AllocatePaymentAcrossComponents
{
    public function __construct(
        private readonly PaymentComponentAllocationReaderPort $existingAllocations,
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
        $existing = $this->existingByComponent($noteId);
        $ordered = $this->sort($components);
        $priority = 1;

        foreach ($ordered as $component) {
            $key = $this->key($component->componentType(), $component->componentRefId());
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
     * @param list<PayableNoteComponent> $components
     * @return list<PayableNoteComponent>
     */
    private function sort(array $components): array
    {
        usort($components, function (PayableNoteComponent $left, PayableNoteComponent $right): int {
            $leftPriority = $this->priority($left->componentType());
            $rightPriority = $this->priority($right->componentType());

            if ($leftPriority !== $rightPriority) {
                return $leftPriority <=> $rightPriority;
            }

            return $left->orderIndex() <=> $right->orderIndex();
        });

        return $components;
    }

    /**
     * @return array<string, int>
     */
    private function existingByComponent(string $noteId): array
    {
        $totals = [];

        foreach ($this->existingAllocations->listByNoteId($noteId) as $allocation) {
            $key = $this->key($allocation->componentType(), $allocation->componentRefId());
            $totals[$key] = ($totals[$key] ?? 0) + $allocation->allocatedAmountRupiah()->amount();
        }

        return $totals;
    }

    private function key(string $componentType, string $componentRefId): string
    {
        return $componentType . '|' . $componentRefId;
    }

    private function priority(string $componentType): int
    {
        return match ($componentType) {
            PaymentComponentType::PRODUCT_ONLY_WORK_ITEM => 1,
            PaymentComponentType::SERVICE_STORE_STOCK_PART => 2,
            PaymentComponentType::SERVICE_EXTERNAL_PURCHASE_PART => 3,
            PaymentComponentType::SERVICE_FEE => 4,
            default => throw new DomainException('Payment component type tidak valid untuk prioritas alokasi.'),
        };
    }
}
