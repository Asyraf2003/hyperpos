<?php

declare(strict_types=1);

namespace App\Application\Note\UseCases;

use App\Core\Note\WorkItem\ServiceDetail;
use App\Core\Note\WorkItem\WorkItem;
use App\Core\Shared\ValueObjects\Money;

final class CreateNoteRevisionServiceItemBuilder
{
    public function __construct(
        private readonly CreateNoteRevisionItemNormalizer $values,
    ) {
    }

    /**
     * @param array<string, mixed> $item
     */
    public function build(string $noteRootId, int $lineNo, array $item): WorkItem
    {
        $serviceName = $this->values->string($item['service_name'] ?? '', 'Service Revision');
        $servicePrice = $this->values->integer($item['service_price'] ?? '0');

        $service = ServiceDetail::create(
            $serviceName === '' ? 'Service Revision' : $serviceName,
            Money::fromInt($servicePrice),
            ServiceDetail::PART_SOURCE_NONE,
        );

        return WorkItem::createServiceOnly(
            sprintf('%s-wi-r%03d', $noteRootId, $lineNo),
            $noteRootId,
            $lineNo,
            $service,
            WorkItem::STATUS_OPEN,
        );
    }
}
