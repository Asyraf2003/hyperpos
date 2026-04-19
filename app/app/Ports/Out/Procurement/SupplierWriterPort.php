<?php

declare(strict_types=1);

namespace App\Ports\Out\Procurement;

use App\Core\Procurement\Supplier\Supplier;

interface SupplierWriterPort
{
    public function create(Supplier $supplier): void;

    public function update(Supplier $supplier): void;
}
