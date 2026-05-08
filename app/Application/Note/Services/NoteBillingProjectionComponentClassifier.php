<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Core\Payment\PaymentComponentAllocation\PaymentComponentType;

final class NoteBillingProjectionComponentClassifier
{
    public function isProduct(string $type): bool
    {
        return in_array($type, [
            PaymentComponentType::PRODUCT_ONLY_WORK_ITEM,
            PaymentComponentType::SERVICE_STORE_STOCK_PART,
            PaymentComponentType::SERVICE_EXTERNAL_PURCHASE_PART,
        ], true);
    }

    public function label(string $type): string
    {
        return match ($type) {
            PaymentComponentType::PRODUCT_ONLY_WORK_ITEM => 'Produk Toko',
            PaymentComponentType::SERVICE_STORE_STOCK_PART => 'Part Toko',
            PaymentComponentType::SERVICE_EXTERNAL_PURCHASE_PART => 'Part External',
            PaymentComponentType::SERVICE_FEE => 'Jasa',
            default => 'Komponen Tagihan',
        };
    }

    public function groupLabel(string $type): string
    {
        return $this->isProduct($type) ? 'Produk' : 'Jasa';
    }
}
