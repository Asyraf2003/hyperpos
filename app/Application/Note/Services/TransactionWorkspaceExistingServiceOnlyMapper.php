<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Core\Note\WorkItem\ServiceDetail;
use App\Core\Note\WorkItem\WorkItem;
use App\Core\Shared\Exceptions\DomainException;

final class TransactionWorkspaceExistingServiceOnlyMapper
{
    /**
     * @return array<string, mixed>
     */
    public function map(WorkItem $workItem): array
    {
        $service = $workItem->serviceDetail();

        if (! $service instanceof ServiceDetail) {
            throw new DomainException('Service detail wajib ada untuk work item service_only.');
        }

        return [
            'entry_mode' => 'service',
            'description' => '',
            'part_source' => $service->partSource(),
            'service' => [
                'name' => $service->serviceName(),
                'price_rupiah' => $service->servicePriceRupiah()->amount(),
                'notes' => '',
            ],
            'product_lines' => [],
            'external_purchase_lines' => [],
        ];
    }
}
