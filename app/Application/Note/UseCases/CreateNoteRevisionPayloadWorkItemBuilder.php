<?php

declare(strict_types=1);

namespace App\Application\Note\UseCases;

use App\Application\Note\Services\CreateTransactionWorkspaceWorkItemPayloadMapper;
use App\Application\Note\Services\WorkItemFactory;
use App\Core\Note\WorkItem\WorkItem;
use App\Ports\Out\ServiceCatalog\ServiceCatalogWriterPort;

final class CreateNoteRevisionPayloadWorkItemBuilder
{
    public function __construct(
        private readonly CreateTransactionWorkspaceWorkItemPayloadMapper $mapper,
        private readonly WorkItemFactory $factory,
        private readonly ServiceCatalogWriterPort $serviceCatalog,
    ) {
    }

    /**
     * @param list<array<string, mixed>> $itemsData
     * @return list<WorkItem>
     */
    public function build(string $noteRootId, array $itemsData): array
    {
        $workItems = [];
        $lineNo = 1;

        foreach ($itemsData as $item) {
            if (! is_array($item)) {
                continue;
            }

            [$type, $service, $external, $store] = $this->mapper->map($item);

            $workItem = $this->factory->build(
                $noteRootId,
                $lineNo,
                $type,
                $service,
                $external,
                $store,
            );

            $this->syncServiceCatalog($workItem);
            $workItems[] = $workItem;
            $lineNo++;
        }

        return $workItems;
    }

    private function syncServiceCatalog(WorkItem $workItem): void
    {
        $service = $workItem->serviceDetail();

        if ($service === null || $service->servicePriceRupiah()->amount() <= 0) {
            return;
        }

        $this->serviceCatalog->createIfMissing(
            $service->serviceName(),
            $service->servicePriceRupiah()->amount()
        );
    }
}
