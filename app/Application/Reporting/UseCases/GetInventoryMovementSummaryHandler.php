<?php

declare(strict_types=1);

namespace App\Application\Reporting\UseCases;

use App\Application\Reporting\Services\InventoryMovementReportingReconciliationService;
use App\Application\Reporting\Services\InventoryMovementSummaryBuilder;
use App\Application\Shared\DTO\Result;
use App\Ports\Out\Reporting\InventoryMovementReportingSourceReaderPort;

final class GetInventoryMovementSummaryHandler
{
    public function __construct(
        private readonly InventoryMovementReportingSourceReaderPort $sourceReader,
        private readonly InventoryMovementSummaryBuilder $builder,
        private readonly InventoryMovementReportingReconciliationService $reconciliation,
    ) {
    }

    public function handle(string $fromMutationDate, string $toMutationDate): Result
    {
        $rawRows = $this->sourceReader->getInventoryMovementSummaryRows(
            $fromMutationDate,
            $toMutationDate,
        );

        $rows = $this->builder->build($rawRows);

        $expected = $this->sourceReader->getInventoryMovementSummaryReconciliation(
            $fromMutationDate,
            $toMutationDate,
        );

        $this->reconciliation->assertInventoryMovementSummaryMatches($rows, $expected);

        return Result::success([
            'rows' => array_map(
                static fn ($row): array => $row->toArray(),
                $rows,
            ),
        ]);
    }
}
