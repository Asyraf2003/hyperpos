<?php

declare(strict_types=1);

namespace App\Application\Inventory\Support;

final class InventoryMovementSourceTypes
{
    public const SUPPLIER_RECEIPT_LINE = 'supplier_receipt_line';
    public const WORK_ITEM_STORE_STOCK_LINE = 'work_item_store_stock_line';
    public const LEGACY_NOTE = 'note';
    public const LEGACY_CUSTOMER_TRANSACTION_LINE = 'customer_transaction_line';
    public const WORK_ITEM_STORE_STOCK_LINE_REVERSAL = 'work_item_store_stock_line_reversal';

    /**
     * @return list<string>
     */
    public static function saleOutSourceTypes(): array
    {
        return [
            self::WORK_ITEM_STORE_STOCK_LINE,
            self::LEGACY_NOTE,
            self::LEGACY_CUSTOMER_TRANSACTION_LINE,
        ];
    }

    /**
     * @return list<string>
     */
    public static function classifiedForReportingSourceTypes(): array
    {
        return [
            self::SUPPLIER_RECEIPT_LINE,
            ...self::saleOutSourceTypes(),
            self::WORK_ITEM_STORE_STOCK_LINE_REVERSAL,
        ];
    }

    public static function supplierReceiptLineSql(): string
    {
        return self::sqlLiteral(self::SUPPLIER_RECEIPT_LINE);
    }

    public static function storeStockLineReversalSql(): string
    {
        return self::sqlLiteral(self::WORK_ITEM_STORE_STOCK_LINE_REVERSAL);
    }

    public static function saleOutSqlList(): string
    {
        return self::sqlList(self::saleOutSourceTypes());
    }

    public static function classifiedForReportingSqlList(): string
    {
        return self::sqlList(self::classifiedForReportingSourceTypes());
    }

    /**
     * @param list<string> $values
     */
    private static function sqlList(array $values): string
    {
        return implode(', ', array_map(self::sqlLiteral(...), $values));
    }

    private static function sqlLiteral(string $value): string
    {
        return "'".str_replace("'", "''", $value)."'";
    }
}
