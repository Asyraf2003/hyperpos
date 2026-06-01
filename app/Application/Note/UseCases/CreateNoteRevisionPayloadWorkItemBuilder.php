<?php

declare(strict_types=1);

namespace App\Application\Note\UseCases;

use App\Application\Note\Services\CreateTransactionWorkspaceWorkItemPayloadMapper;
use App\Application\Note\Services\WorkItemFactory;
use App\Core\Note\WorkItem\WorkItem;

final class CreateNoteRevisionPayloadWorkItemBuilder
{
    public function __construct(
        private readonly CreateTransactionWorkspaceWorkItemPayloadMapper $mapper,
        private readonly WorkItemFactory $factory,
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

            $workItems[] = $this->factory->build(
                $noteRootId,
                $lineNo,
                $type,
                $service,
                $external,
                $store,
            );

            $lineNo++;
        }

        return $workItems;
    }
}
