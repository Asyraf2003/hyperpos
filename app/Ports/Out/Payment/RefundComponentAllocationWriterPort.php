<?php

declare(strict_types=1);

namespace App\Ports\Out\Payment;

use App\Core\Payment\RefundComponentAllocation\RefundComponentAllocation;

interface RefundComponentAllocationWriterPort
{
    /**
     * @param list<RefundComponentAllocation> $allocations
     */
    public function createMany(array $allocations): void;
}
