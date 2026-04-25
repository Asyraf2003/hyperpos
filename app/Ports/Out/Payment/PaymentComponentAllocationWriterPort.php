<?php

declare(strict_types=1);

namespace App\Ports\Out\Payment;

use App\Core\Payment\PaymentComponentAllocation\PaymentComponentAllocation;

interface PaymentComponentAllocationWriterPort
{
    /**
     * @param list<PaymentComponentAllocation> $allocations
     */
    public function createMany(array $allocations): void;

    public function deleteByNoteId(string $noteId): void;
}
