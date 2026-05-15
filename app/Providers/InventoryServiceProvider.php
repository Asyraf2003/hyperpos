<?php

declare(strict_types=1);

namespace App\Providers;

use App\Adapters\Out\Inventory\DatabaseInventoryMovementReaderAdapter;
use App\Adapters\Out\Inventory\DatabaseInventoryMovementWriterAdapter;
use App\Adapters\Out\Inventory\DatabaseProductInventoryCostingProjectionWriterAdapter;
use App\Adapters\Out\Inventory\DatabaseProductInventoryCostingReaderAdapter;
use App\Adapters\Out\Inventory\DatabaseProductInventoryCostingWriterAdapter;
use App\Adapters\Out\Inventory\DatabaseProductInventoryProjectionWriterAdapter;
use App\Adapters\Out\Inventory\DatabaseProductInventoryReaderAdapter;
use App\Adapters\Out\Inventory\DatabaseProductInventoryWriterAdapter;
use App\Application\Inventory\Policies\DefaultNegativeStockPolicy;
use App\Application\Inventory\Services\InventoryCostingProjectionBuilder;
use App\Application\Inventory\Services\InventoryProjectionBuilder;
use App\Application\Inventory\Services\InventoryProjectionService;
use App\Application\Inventory\Services\IssueInventoryOperation;
use App\Core\Inventory\Policies\NegativeStockPolicy;
use App\Ports\Out\Inventory\InventoryMovementReaderPort;
use App\Ports\Out\Inventory\InventoryMovementWriterPort;
use App\Ports\Out\Inventory\ProductInventoryCostingProjectionWriterPort;
use App\Ports\Out\Inventory\ProductInventoryCostingReaderPort;
use App\Ports\Out\Inventory\ProductInventoryCostingWriterPort;
use App\Ports\Out\Inventory\ProductInventoryProjectionWriterPort;
use App\Ports\Out\Inventory\ProductInventoryReaderPort;
use App\Ports\Out\Inventory\ProductInventoryWriterPort;
use Illuminate\Support\ServiceProvider;

class InventoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(NegativeStockPolicy::class, DefaultNegativeStockPolicy::class);

        $this->app->singleton(InventoryProjectionService::class);
        $this->app->singleton(IssueInventoryOperation::class);
        $this->app->singleton(InventoryCostingProjectionBuilder::class);
        $this->app->singleton(InventoryProjectionBuilder::class);

        $this->app->singleton(InventoryMovementReaderPort::class, DatabaseInventoryMovementReaderAdapter::class);
        $this->app->singleton(InventoryMovementWriterPort::class, DatabaseInventoryMovementWriterAdapter::class);
        $this->app->singleton(ProductInventoryReaderPort::class, DatabaseProductInventoryReaderAdapter::class);
        $this->app->singleton(ProductInventoryWriterPort::class, DatabaseProductInventoryWriterAdapter::class);
        $this->app->singleton(ProductInventoryProjectionWriterPort::class, DatabaseProductInventoryProjectionWriterAdapter::class);
        $this->app->singleton(ProductInventoryCostingReaderPort::class, DatabaseProductInventoryCostingReaderAdapter::class);
        $this->app->singleton(ProductInventoryCostingWriterPort::class, DatabaseProductInventoryCostingWriterAdapter::class);
        $this->app->singleton(ProductInventoryCostingProjectionWriterPort::class, DatabaseProductInventoryCostingProjectionWriterAdapter::class);
    }
}
