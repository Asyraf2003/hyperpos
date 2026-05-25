<?php

declare(strict_types=1);

namespace Tests\Feature\Reporting;

use App\Application\Reporting\Services\TransactionCashLedgerPeriodTableBuilder;
use Tests\TestCase;

final class TransactionCashLedgerPeriodTableBuilderFeatureTest extends TestCase
{
    public function test_period_table_builder_splits_cash_and_transfer_money_in_per_date(): void
    {
        $rows = app(TransactionCashLedgerPeriodTableBuilder::class)->build([
            [
                'event_date' => '2026-04-02',
                'direction' => 'in',
                'event_amount_rupiah' => 85000,
                'payment_method' => 'cash',
            ],
            [
                'event_date' => '2026-04-02',
                'direction' => 'in',
                'event_amount_rupiah' => 30000,
                'payment_method' => 'transfer',
            ],
            [
                'event_date' => '2026-04-02',
                'direction' => 'out',
                'event_amount_rupiah' => 10000,
                'payment_method' => null,
            ],
            [
                'event_date' => '2026-04-03',
                'direction' => 'in',
                'event_amount_rupiah' => 40000,
                'payment_method' => 'transfer',
            ],
        ]);

        $this->assertSame([
            [
                'period_label' => '2026-04-02',
                'total_events' => 3,
                'total_in_rupiah' => 115000,
                'cash_in_rupiah' => 85000,
                'transfer_in_rupiah' => 30000,
                'cash_out_rupiah' => 10000,
                'net_amount_rupiah' => 105000,
            ],
            [
                'period_label' => '2026-04-03',
                'total_events' => 1,
                'total_in_rupiah' => 40000,
                'cash_in_rupiah' => 0,
                'transfer_in_rupiah' => 40000,
                'cash_out_rupiah' => 0,
                'net_amount_rupiah' => 40000,
            ],
        ], $rows);
    }
}
