<?php

declare(strict_types=1);

namespace Tests\Feature\Reporting;

use App\Application\Reporting\UseCases\GetDashboardOperationalPerformanceDatasetHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class CashChangeDenominationDashboardDatasetFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_operational_performance_includes_cash_change_denomination_breakdown(): void
    {
        DB::table('customer_payments')->insert([
            [
                'id' => 'payment-dashboard-denomination-1',
                'amount_rupiah' => 100000,
                'payment_method' => 'cash',
                'paid_at' => '2026-04-05 09:00:00',
            ],
            [
                'id' => 'payment-dashboard-denomination-2',
                'amount_rupiah' => 50000,
                'payment_method' => 'cash',
                'paid_at' => '2026-04-05 10:00:00',
            ],
            [
                'id' => 'payment-dashboard-denomination-3',
                'amount_rupiah' => 500,
                'payment_method' => 'cash',
                'paid_at' => '2026-04-05 11:00:00',
            ],
            [
                'id' => 'payment-dashboard-denomination-outside',
                'amount_rupiah' => 70000,
                'payment_method' => 'cash',
                'paid_at' => '2026-04-06 09:00:00',
            ],
        ]);

        DB::table('customer_payment_cash_details')->insert([
            [
                'customer_payment_id' => 'payment-dashboard-denomination-1',
                'amount_paid_rupiah' => 100000,
                'amount_received_rupiah' => 137000,
                'change_rupiah' => 37000,
            ],
            [
                'customer_payment_id' => 'payment-dashboard-denomination-2',
                'amount_paid_rupiah' => 50000,
                'amount_received_rupiah' => 70000,
                'change_rupiah' => 20000,
            ],
            [
                'customer_payment_id' => 'payment-dashboard-denomination-3',
                'amount_paid_rupiah' => 500,
                'amount_received_rupiah' => 1000,
                'change_rupiah' => 500,
            ],
            [
                'customer_payment_id' => 'payment-dashboard-denomination-outside',
                'amount_paid_rupiah' => 70000,
                'amount_received_rupiah' => 100000,
                'change_rupiah' => 30000,
            ],
        ]);

        $dataset = app(GetDashboardOperationalPerformanceDatasetHandler::class)
            ->handle('2026-04-05', '2026-04-05');

        $this->assertSame(57500, $dataset['summary']['total_potential_change_rupiah']);
        $this->assertSame(
            [
                ['denomination' => 20000, 'count' => 2, 'total_rupiah' => 40000],
                ['denomination' => 10000, 'count' => 1, 'total_rupiah' => 10000],
                ['denomination' => 5000, 'count' => 1, 'total_rupiah' => 5000],
                ['denomination' => 2000, 'count' => 1, 'total_rupiah' => 2000],
                ['denomination' => 500, 'count' => 1, 'total_rupiah' => 500],
            ],
            $dataset['cash_change_denominations'],
        );
    }
}
