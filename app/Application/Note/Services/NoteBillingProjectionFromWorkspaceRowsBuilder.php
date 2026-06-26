<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

final class NoteBillingProjectionFromWorkspaceRowsBuilder
{
    public function __construct(
        private readonly NoteBillingProjectionComponentRowsBuilder $componentRows,
        private readonly NoteBillingProjectionLineTotalRowBuilder $lineTotals,
    ) {
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @return list<array<string, mixed>>
     */
    public function build(array $rows): array
    {
        $billing = [];

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            if (trim((string) ($row['status'] ?? '')) === 'canceled') {
                continue;
            }

            $componentRows = $this->componentRows->build($row);

            if ($componentRows !== []) {
                array_push($billing, ...$componentRows);
                continue;
            }

            $billing[] = $this->lineTotals->build($row);
        }

        return $billing;
    }
}
