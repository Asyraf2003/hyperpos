<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Inventory\Support;

use App\Application\Inventory\Support\InventoryMovementSourceTypes;
use PHPUnit\Framework\TestCase;

final class InventoryMovementSourceTypesTest extends TestCase
{
    public function test_reporting_bucket_source_types_are_stable(): void
    {
        $this->assertSame(
            ['work_item_store_stock_line', 'note', 'customer_transaction_line'],
            InventoryMovementSourceTypes::saleOutSourceTypes()
        );

        $this->assertSame(
            [
                'supplier_receipt_line',
                'work_item_store_stock_line',
                'note',
                'customer_transaction_line',
                'work_item_store_stock_line_reversal',
            ],
            InventoryMovementSourceTypes::classifiedForReportingSourceTypes()
        );
    }

    public function test_reporting_sql_lists_are_deterministic_and_quoted(): void
    {
        $this->assertSame(
            "'work_item_store_stock_line', 'note', 'customer_transaction_line'",
            InventoryMovementSourceTypes::saleOutSqlList()
        );

        $this->assertSame(
            "'supplier_receipt_line', 'work_item_store_stock_line', 'note', 'customer_transaction_line', 'work_item_store_stock_line_reversal'",
            InventoryMovementSourceTypes::classifiedForReportingSqlList()
        );
    }
}
