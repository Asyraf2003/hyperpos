<?php

declare(strict_types=1);

namespace App\Adapters\Out\ServiceProductTemplate;

use App\Application\ServiceProductTemplate\DTO\ServiceProductTemplateLookupRow;
use App\Application\ServiceProductTemplate\DTO\ServiceProductTemplatePackageLookupRow;
use App\Ports\Out\ServiceProductTemplate\ServiceProductTemplateLookupReaderPort;

final class DatabaseServiceProductTemplateLookupReaderAdapter implements ServiceProductTemplateLookupReaderPort
{
    public function __construct(
        private readonly ActiveServiceProductTemplateLookupQuery $activeLookup,
        private readonly ServiceProductTemplateLookupRowMapper $lookupRows,
        private readonly DatabaseServiceProductTemplatePackageSearchQuery $packageSearch,
        private readonly DatabaseServiceProductTemplatePackageMapper $packageRows,
    ) {
    }

    public function findActiveByProductId(string $productId): ?ServiceProductTemplateLookupRow
    {
        $row = $this->activeLookup->firstByProductId($productId);

        return $row === null ? null : $this->lookupRows->map($row);
    }

    public function findActivePackageByProductId(string $productId): ?ServiceProductTemplatePackageLookupRow
    {
        $row = $this->activeLookup->firstByProductId($productId);

        if ($row === null) {
            return null;
        }

        return $this->packageRows->map((object) [
            'id' => $row->id,
            'legacy_product_id' => $row->product_id,
            'service_catalog_item_id' => $row->service_catalog_item_id,
            'default_service_price_rupiah' => $row->default_service_price_rupiah,
            'default_package_total_rupiah' => $row->default_package_total_rupiah,
            'is_active' => $row->is_active,
            'service_name' => $row->service_name,
        ]);
    }

    /**
     * @return list<ServiceProductTemplatePackageLookupRow>
     */
    public function searchActivePackages(
        string $query,
        int $limit = ServiceProductTemplateLookupReaderPort::DEFAULT_PACKAGE_LIMIT,
    ): array {
        $packages = [];

        foreach ($this->packageSearch->search($query, $limit) as $row) {
            $package = $this->packageRows->map($row);

            if ($package !== null) {
                $packages[] = $package;
            }
        }

        return $packages;
    }
}
