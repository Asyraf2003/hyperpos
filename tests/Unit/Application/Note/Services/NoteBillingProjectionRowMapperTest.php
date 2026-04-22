<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Note\Services;

use App\Application\Note\Services\NoteBillingProjectionRowMapper;
use App\Application\Note\Services\NoteBillingProjectionSupport;
use App\Application\Payment\DTO\PayableNoteComponent;
use App\Core\Note\WorkItem\ExternalPurchaseLine;
use App\Core\Note\WorkItem\ServiceDetail;
use App\Core\Note\WorkItem\WorkItem;
use App\Core\Shared\ValueObjects\Money;
use PHPUnit\Framework\TestCase;

final class NoteBillingProjectionRowMapperTest extends TestCase
{
    public function test_it_maps_service_component_as_outstanding_and_selectable(): void
    {
        $mapper = new NoteBillingProjectionRowMapper(new NoteBillingProjectionSupport());

        $item = WorkItem::createServiceOnly(
            'wi-1',
            'note-1',
            1,
            ServiceDetail::create('Servis Mesin', Money::fromInt(50000), ServiceDetail::PART_SOURCE_NONE),
        );

        $component = new PayableNoteComponent(
            'wi-1',
            'service_fee',
            'wi-1',
            Money::fromInt(50000),
            1,
        );

        $lineOutstanding = [];

        $row = $mapper->map($component, $item, [], [], $lineOutstanding);

        self::assertSame('wi-1', $row['work_item_id']);
        self::assertSame('Jasa', $row['component_label']);
        self::assertSame(50000, $row['outstanding_rupiah']);
        self::assertTrue($row['can_select_manually']);
        self::assertFalse($row['eligible_for_dp_preset']);
        self::assertSame('Belum Dibayar', $row['status_label']);
    }

    public function test_it_blocks_next_component_on_same_line_when_previous_component_still_outstanding(): void
    {
        $mapper = new NoteBillingProjectionRowMapper(new NoteBillingProjectionSupport());

        $item = WorkItem::createServiceWithExternalPurchase(
            'wi-2',
            'note-1',
            2,
            ServiceDetail::create('Servis Board', Money::fromInt(30000), ServiceDetail::PART_SOURCE_NONE),
            [ExternalPurchaseLine::create('ext-1', 'IC Board', Money::fromInt(70000), 1)],
        );

        $productComponent = new PayableNoteComponent(
            'wi-2',
            'service_external_purchase_part',
            'ext-1',
            Money::fromInt(70000),
            1,
        );

        $serviceComponent = new PayableNoteComponent(
            'wi-2',
            'service_fee',
            'wi-2',
            Money::fromInt(30000),
            2,
        );

        $lineOutstanding = [];

        $first = $mapper->map($productComponent, $item, [], [], $lineOutstanding);
        $second = $mapper->map($serviceComponent, $item, [], [], $lineOutstanding);

        self::assertTrue($first['eligible_for_dp_preset']);
        self::assertTrue($first['can_select_manually']);
        self::assertSame('Part External', $first['component_label']);

        self::assertFalse($second['can_select_manually']);
        self::assertSame('Komponen sebelumnya pada line ini belum lunas. Ikuti urutan tagihan existing.', $second['selection_blocked_reason']);
        self::assertSame('Jasa', $second['component_label']);
    }

    public function test_it_marks_component_paid_when_net_paid_covers_total(): void
    {
        $mapper = new NoteBillingProjectionRowMapper(new NoteBillingProjectionSupport());

        $item = WorkItem::createServiceOnly(
            'wi-3',
            'note-1',
            3,
            ServiceDetail::create('Servis Rem', Money::fromInt(40000), ServiceDetail::PART_SOURCE_NONE),
        );

        $component = new PayableNoteComponent(
            'wi-3',
            'service_fee',
            'wi-3',
            Money::fromInt(40000),
            1,
        );

        $lineOutstanding = [];
        $paid = ['service_fee::wi-3' => 40000];
        $refunded = ['service_fee::wi-3' => 0];

        $row = $mapper->map($component, $item, $paid, $refunded, $lineOutstanding);

        self::assertTrue($row['is_paid']);
        self::assertSame(0, $row['outstanding_rupiah']);
        self::assertSame('Lunas', $row['status_label']);
    }
}
