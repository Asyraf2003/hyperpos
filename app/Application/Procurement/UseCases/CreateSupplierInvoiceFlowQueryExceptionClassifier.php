<?php

declare(strict_types=1);

namespace App\Application\Procurement\UseCases;

use App\Application\Procurement\Services\SupplierInvoiceQueryExceptionClassifier;
use App\Application\Shared\DTO\Result;
use Illuminate\Database\QueryException;

final class CreateSupplierInvoiceFlowQueryExceptionClassifier
{
    public function __construct(
        private readonly SupplierInvoiceQueryExceptionClassifier $classifier,
    ) {
    }

    public function classify(QueryException $e): ?Result
    {
        return $this->classifier->classify($e);
    }
}
