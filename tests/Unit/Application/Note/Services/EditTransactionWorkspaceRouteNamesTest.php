<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Note\Services;

use App\Application\Note\Services\EditTransactionWorkspaceRouteNames;
use PHPUnit\Framework\TestCase;

final class EditTransactionWorkspaceRouteNamesTest extends TestCase
{
    public function test_it_resolves_package_lookup_routes_for_cashier_and_admin_edit_workspace(): void
    {
        $routes = new EditTransactionWorkspaceRouteNames();

        self::assertSame(
            'cashier.notes.packages.lookup',
            $routes->resolve('cashier')['packages_lookup'],
        );

        self::assertSame(
            'admin.notes.packages.lookup',
            $routes->resolve('admin')['packages_lookup'],
        );
    }
}
