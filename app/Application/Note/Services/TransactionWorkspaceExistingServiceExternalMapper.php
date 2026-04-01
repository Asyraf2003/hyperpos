<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Core\Note\WorkItem\ExternalPurchaseLine;
use App\Core\Note\WorkItem\ServiceDetail;
use App\Core\Note\WorkItem\WorkItem;
use App\Core\Shared\Exceptions\DomainException;

final class TransactionWorkspaceExistingServiceExternalMapper
{
    /**
     * @return array<string, mixed>
     */
    public function map(WorkItem $workItem): array
    {
        $service = $workItem->serviceDetail();

        if (! $service instanceof ServiceDetail) {
            throw new DomainException('Service detail wajib ada untuk work item service external.');
        }

        $externalLines = $workItem->externalPurchaseLines();

        if (count($externalLines) !== 1) {
            throw new DomainException('Workspace edit hanya mendukung 1 external purchase line per item.');
        }

        $line = $externalLines[0];

        if (! $line instanceof ExternalPurchaseLine) {
            throw new DomainException('External purchase line tidak valid.');
        }

        return [
            'entry_mode' => 'service',
            'description' => '',
            'part_source' => 'external_purchase',
            'service' => [
                'name' => $service->serviceName(),
                'price_rupiah' => $service->servicePriceRupiah()->amount(),
                'notes' => '',
            ],
            'product_lines' => [],
            'external_purchase_lines' => [[
                'label' => $line->costDescription(),
                'qty' => $line->qty(),
                'unit_cost_rupiah' => $line->unitCostRupiah()->amount(),
            ]],
        ];
    }
}
