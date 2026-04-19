<?php

declare(strict_types=1);

namespace App\Application\Procurement\UseCases;

use App\Application\Procurement\DTO\SupplierTableQuery;
use App\Application\Shared\DTO\Result;
use App\Ports\Out\Procurement\SupplierTableReaderPort;

final class GetSupplierTableHandler
{
    public function __construct(
        private readonly SupplierTableReaderPort $suppliers,
    ) {
    }

    public function handle(SupplierTableQuery $query): Result
    {
        return Result::success($this->suppliers->search($query));
    }
}
