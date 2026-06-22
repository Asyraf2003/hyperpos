<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Inventory\Movement;

use App\Core\Inventory\Movement\InventoryMovement;
use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class InventoryMovementCostRevaluationTest extends TestCase
{
    public function test_cost_revaluation_can_be_value_only_with_zero_quantity(): void
    {
        $movement = InventoryMovement::createValueOnly(
            'movement-1',
            'product-1',
            'cost_revaluation',
            'supplier_invoice_cost_revaluation',
            'invoice-line-2',
            new DateTimeImmutable('2026-03-17'),
            Money::fromInt(2000),
        );

        $this->assertSame('cost_revaluation', $movement->movementType());
        $this->assertSame('supplier_invoice_cost_revaluation', $movement->sourceType());
        $this->assertSame(0, $movement->qtyDelta());
        $this->assertSame(0, $movement->unitCostRupiah()->amount());
        $this->assertSame(2000, $movement->totalCostRupiah()->amount());
    }

    public function test_stock_movement_still_rejects_zero_quantity(): void
    {
        $this->expectException(DomainException::class);

        InventoryMovement::create(
            'movement-1',
            'product-1',
            'stock_in',
            'supplier_receipt_line',
            'receipt-line-1',
            new DateTimeImmutable('2026-03-16'),
            0,
            Money::fromInt(10000),
        );
    }

    public function test_cost_revaluation_rejects_non_zero_quantity(): void
    {
        $this->expectException(DomainException::class);

        InventoryMovement::create(
            'movement-1',
            'product-1',
            'cost_revaluation',
            'supplier_invoice_cost_revaluation',
            'invoice-line-2',
            new DateTimeImmutable('2026-03-17'),
            1,
            Money::zero(),
        );
    }
}
