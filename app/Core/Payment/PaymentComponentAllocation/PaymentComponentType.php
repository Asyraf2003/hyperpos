<?php

declare(strict_types=1);

namespace App\Core\Payment\PaymentComponentAllocation;

use App\Core\Shared\Exceptions\DomainException;

final class PaymentComponentType
{
    public const PRODUCT_ONLY_WORK_ITEM = 'product_only_work_item';
    public const SERVICE_FEE = 'service_fee';
    public const SERVICE_STORE_STOCK_PART = 'service_store_stock_part';
    public const SERVICE_EXTERNAL_PURCHASE_PART = 'service_external_purchase_part';

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [
            self::PRODUCT_ONLY_WORK_ITEM,
            self::SERVICE_FEE,
            self::SERVICE_STORE_STOCK_PART,
            self::SERVICE_EXTERNAL_PURCHASE_PART,
        ];
    }

    public static function assertValid(string $value): void
    {
        if (in_array(trim($value), self::all(), true)) {
            return;
        }

        throw new DomainException('Payment component type tidak valid.');
    }
}
