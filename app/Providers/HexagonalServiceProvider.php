<?php

declare(strict_types=1);

namespace App\Providers;

use App\Adapters\Out\Audit\DatabaseAuditLogAdapter;
use App\Adapters\Out\Auth\LaravelUuidAdapter;
use App\Adapters\Out\Clock\SystemClockAdapter;
use App\Adapters\Out\IdentityAccess\DatabaseActorAccessReaderAdapter;
use App\Adapters\Out\IdentityAccess\DatabaseAdminTransactionCapabilityStateAdapter;
use App\Adapters\Out\Persistence\DatabaseTransactionManagerAdapter;
use App\Adapters\Out\Policy\NullCapabilityPolicyAdapter;
use App\Adapters\Out\Procurement\DatabaseSupplierInvoiceWriterAdapter;
use App\Adapters\Out\Procurement\DatabaseSupplierReaderAdapter;
use App\Adapters\Out\Procurement\DatabaseSupplierWriterAdapter;
use App\Adapters\Out\ProductCatalog\DatabaseProductDuplicateCheckerAdapter;
use App\Adapters\Out\ProductCatalog\DatabaseProductReaderAdapter;
use App\Adapters\Out\ProductCatalog\DatabaseProductWriterAdapter;
use App\Application\System\Health\HealthCheckHandler;
use App\Ports\In\HealthCheckUseCase;
use App\Ports\Out\AuditLogPort;
use App\Ports\Out\CapabilityPolicyPort;
use App\Ports\Out\ClockPort;
use App\Ports\Out\IdentityAccess\ActorAccessReaderPort;
use App\Ports\Out\IdentityAccess\AdminTransactionCapabilityStatePort;
use App\Ports\Out\Procurement\SupplierInvoiceWriterPort;
use App\Ports\Out\Procurement\SupplierReaderPort;
use App\Ports\Out\Procurement\SupplierWriterPort;
use App\Ports\Out\ProductCatalog\ProductDuplicateCheckerPort;
use App\Ports\Out\ProductCatalog\ProductReaderPort;
use App\Ports\Out\ProductCatalog\ProductWriterPort;
use App\Ports\Out\TransactionManagerPort;
use App\Ports\Out\UuidPort;
use Illuminate\Support\ServiceProvider;

class HexagonalServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(HealthCheckUseCase::class, HealthCheckHandler::class);

        $this->app->singleton(ClockPort::class, SystemClockAdapter::class);
        $this->app->singleton(UuidPort::class, LaravelUuidAdapter::class);
        $this->app->singleton(AuditLogPort::class, DatabaseAuditLogAdapter::class);
        $this->app->singleton(CapabilityPolicyPort::class, NullCapabilityPolicyAdapter::class);
        $this->app->singleton(TransactionManagerPort::class, DatabaseTransactionManagerAdapter::class);

        $this->app->singleton(ActorAccessReaderPort::class, DatabaseActorAccessReaderAdapter::class);
        $this->app->singleton(AdminTransactionCapabilityStatePort::class, DatabaseAdminTransactionCapabilityStateAdapter::class);

        $this->app->singleton(ProductReaderPort::class, DatabaseProductReaderAdapter::class);
        $this->app->singleton(ProductWriterPort::class, DatabaseProductWriterAdapter::class);
        $this->app->singleton(ProductDuplicateCheckerPort::class, DatabaseProductDuplicateCheckerAdapter::class);

        $this->app->singleton(SupplierReaderPort::class, DatabaseSupplierReaderAdapter::class);
        $this->app->singleton(SupplierWriterPort::class, DatabaseSupplierWriterAdapter::class);
        $this->app->singleton(SupplierInvoiceWriterPort::class, DatabaseSupplierInvoiceWriterAdapter::class);
    }
}
