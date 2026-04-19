<?php

declare(strict_types=1);

namespace App\Core\ProductCatalog\Policies;

use App\Core\ProductCatalog\Product\Product;
use App\Core\Shared\ValueObjects\Money;
use App\Core\Shared\Exceptions\DomainException;

final class MinSellingPricePolicy
{
    public function assertAllowed(Product $product, int $qty, Money $actualTotal): void
    {
        if ($qty <= 0) throw new DomainException('Qty harus lebih besar dari nol.');
        
        $minTotal = $product->hargaJual()->amount() * $qty;
        if ($actualTotal->amount() < $minTotal) {
            throw new DomainException('Harga jual pada store stock line tidak boleh di bawah harga jual minimum.');
        }
    }
}
