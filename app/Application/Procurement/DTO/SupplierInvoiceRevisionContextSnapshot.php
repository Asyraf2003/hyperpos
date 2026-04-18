<?php

declare(strict_types=1);

namespace App\Application\Procurement\DTO;

use DateTimeImmutable;

final class SupplierInvoiceRevisionContextSnapshot
{
    public function __construct(
        private readonly int $totalPaidRupiah,
        private readonly int $totalReceivedQty,
        private readonly DateTimeImmutable $movementDate,
    ) {
    }

    public function totalPaidRupiah(): int
    {
        return $this->totalPaidRupiah;
    }

    public function totalReceivedQty(): int
    {
        return $this->totalReceivedQty;
    }

    public function movementDate(): DateTimeImmutable
    {
        return $this->movementDate;
    }
}
