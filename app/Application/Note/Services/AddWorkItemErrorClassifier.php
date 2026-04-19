<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Application\Shared\DTO\Result;
use App\Core\Shared\Exceptions\DomainException;

final class AddWorkItemErrorClassifier
{
    public function classify(DomainException $e): Result
    {
        $msg = $e->getMessage();
        $code = 'INVALID_WORK_ITEM';
        $key = 'work_item';

        if (str_contains($msg, 'Stok inventory tidak cukup')) {
            $code = 'INVENTORY_INSUFFICIENT_STOCK';
            $key = 'inventory';
        } elseif (str_contains($msg, 'harga jual minimum')) {
            $code = 'PRICING_BELOW_MINIMUM_SELLING_PRICE';
            $key = 'pricing';
        } elseif (str_contains($msg, 'note yang sudah lunas')) {
            $code = 'NOTE_NEW_ITEMS_NOT_ALLOWED_AFTER_PAID';
            $key = 'note';
        }

        return Result::failure($msg, [$key => [$code]]);
    }
}
