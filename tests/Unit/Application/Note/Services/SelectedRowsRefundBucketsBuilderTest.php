<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Note\Services;

use App\Application\Note\Services\SelectedRowsRefundBucketsBuilder;
use App\Core\Payment\PaymentComponentAllocation\PaymentComponentAllocation;
use App\Core\Payment\PaymentComponentAllocation\PaymentComponentType;
use App\Core\Payment\RefundComponentAllocation\RefundComponentAllocation;
use App\Core\Shared\ValueObjects\Money;
use PHPUnit\Framework\TestCase;

final class SelectedRowsRefundBucketsBuilderTest extends TestCase
{
    public function test_it_uses_remaining_refundable_amount_for_selected_rows(): void
    {
        $builder = new SelectedRowsRefundBucketsBuilder();

        $buckets = $builder->build(
            ['wi-1'],
            [
                PaymentComponentAllocation::rehydrate(
                    'pca-1',
                    'payment-1',
                    'note-1',
                    'wi-1',
                    PaymentComponentType::PRODUCT_ONLY_WORK_ITEM,
                    'wi-1',
                    Money::fromInt(100000),
                    Money::fromInt(100000),
                    1,
                ),
            ],
            [
                RefundComponentAllocation::rehydrate(
                    'rca-1',
                    'refund-1',
                    'payment-1',
                    'note-1',
                    'wi-1',
                    PaymentComponentType::PRODUCT_ONLY_WORK_ITEM,
                    'wi-1',
                    Money::fromInt(40000),
                    1,
                ),
            ],
        );

        self::assertCount(1, $buckets);
        self::assertSame('payment-1', $buckets[0]->customerPaymentId());
        self::assertSame(['wi-1'], $buckets[0]->rowIds());
        self::assertSame(60000, $buckets[0]->amountRupiah());
    }

    public function test_it_skips_fully_refunded_selected_rows(): void
    {
        $builder = new SelectedRowsRefundBucketsBuilder();

        $buckets = $builder->build(
            ['wi-1'],
            [
                PaymentComponentAllocation::rehydrate(
                    'pca-1',
                    'payment-1',
                    'note-1',
                    'wi-1',
                    PaymentComponentType::PRODUCT_ONLY_WORK_ITEM,
                    'wi-1',
                    Money::fromInt(100000),
                    Money::fromInt(100000),
                    1,
                ),
            ],
            [
                RefundComponentAllocation::rehydrate(
                    'rca-1',
                    'refund-1',
                    'payment-1',
                    'note-1',
                    'wi-1',
                    PaymentComponentType::PRODUCT_ONLY_WORK_ITEM,
                    'wi-1',
                    Money::fromInt(100000),
                    1,
                ),
            ],
        );

        self::assertSame([], $buckets);
    }
}
