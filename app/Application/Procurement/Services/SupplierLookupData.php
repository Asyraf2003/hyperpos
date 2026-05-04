<?php

declare(strict_types=1);

namespace App\Application\Procurement\Services;

use App\Core\Procurement\Supplier\Supplier;
use App\Ports\Out\Procurement\SupplierReaderPort;

final class SupplierLookupData
{
    public function __construct(
        private readonly SupplierReaderPort $suppliers,
    ) {
    }

    /**
     * @return list<Supplier>
     */
    public function search(string $search): array
    {
        return $this->suppliers->search(trim($search));
    }
}
