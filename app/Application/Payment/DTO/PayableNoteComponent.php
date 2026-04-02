<?php

declare(strict_types=1);

namespace App\Application\Payment\DTO;

use App\Core\Payment\PaymentComponentAllocation\PaymentComponentType;
use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;

final class PayableNoteComponent
{
    public function __construct(
        private readonly string $workItemId,
        private readonly string $componentType,
        private readonly string $componentRefId,
        private readonly Money $amountRupiah,
        private readonly int $orderIndex,
    ) {
        if (trim($this->workItemId) === '') throw new DomainException('Work item id komponen wajib ada.');
        if (trim($this->componentRefId) === '') throw new DomainException('Component ref id wajib ada.');
        if ($this->orderIndex <= 0) throw new DomainException('Order index komponen harus > 0.');
        if (!$this->amountRupiah->greaterThan(Money::zero())) throw new DomainException('Amount komponen harus > 0.');
        PaymentComponentType::assertValid($this->componentType);
    }

    public function workItemId(): string { return $this->workItemId; }
    public function componentType(): string { return $this->componentType; }
    public function componentRefId(): string { return $this->componentRefId; }
    public function amountRupiah(): Money { return $this->amountRupiah; }
    public function orderIndex(): int { return $this->orderIndex; }
}
