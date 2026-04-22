<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Note\Services;

use App\Application\Note\Services\NoteDetailRowMapper;
use App\Application\Note\Services\NoteDetailRowPresentationSupport;
use App\Application\Note\Services\WorkItemOperationalStatusResolver;
use App\Core\Note\WorkItem\ExternalPurchaseLine;
use App\Core\Note\WorkItem\ServiceDetail;
use App\Core\Note\WorkItem\WorkItem;
use App\Core\Shared\ValueObjects\Money;
use PHPUnit\Framework\TestCase;

final class NoteDetailRowMapperTest extends TestCase
{
    public function test_it_maps_service_only_row_for_hybrid_read_model(): void
    {
        $mapper = new NoteDetailRowMapper(
            new WorkItemOperationalStatusResolver(),
            new NoteDetailRowPresentationSupport(),
        );

        $row = $mapper->map([
            WorkItem::createServiceOnly(
                'wi-1',
                'note-1',
                1,
                ServiceDetail::create('Servis Oli', Money::fromInt(50000), ServiceDetail::PART_SOURCE_NONE),
            ),
        ], [
            'wi-1' => [
                'allocated_rupiah' => 20000,
                'refunded_rupiah' => 0,
                'net_paid_rupiah' => 20000,
                'outstanding_rupiah' => 30000,
                'settlement_label' => 'partial',
            ],
        ]);

        self::assertCount(1, $row);
        self::assertSame('Service Only', $row[0]['type_label']);
        self::assertSame(30000, $row[0]['outstanding_rupiah']);
        self::assertSame('open', $row[0]['line_status']);
        self::assertFalse($row[0]['can_refund']);
    }

    public function test_it_builds_refund_preview_for_external_purchase_line(): void
    {
        $mapper = new NoteDetailRowMapper(
            new WorkItemOperationalStatusResolver(),
            new NoteDetailRowPresentationSupport(),
        );

        $rows = $mapper->map([
            WorkItem::createServiceWithExternalPurchase(
                'wi-2',
                'note-1',
                2,
                ServiceDetail::create('Servis AC', Money::fromInt(30000), ServiceDetail::PART_SOURCE_NONE),
                [ExternalPurchaseLine::create('ext-1', 'Kompresor', Money::fromInt(70000), 1)],
            ),
        ], [
            'wi-2' => [
                'allocated_rupiah' => 100000,
                'refunded_rupiah' => 0,
                'net_paid_rupiah' => 100000,
                'outstanding_rupiah' => 0,
                'settlement_label' => 'paid',
            ],
        ]);

        self::assertSame('Service + Part External', $rows[0]['type_label']);
        self::assertSame(1, $rows[0]['refund_external_count']);
        self::assertSame('Uang balik mungkin, external tidak memicu stok toko.', $rows[0]['refund_preview_label']);
    }
}
