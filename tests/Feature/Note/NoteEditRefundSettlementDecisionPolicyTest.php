<?php

declare(strict_types=1);

namespace Tests\Feature\Note;

use App\Application\Note\Services\NoteDetailPageDataBuilder;
use App\Core\Note\WorkItem\ServiceDetail;
use App\Core\Note\WorkItem\WorkItem;
use App\Core\Payment\PaymentComponentAllocation\PaymentComponentType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\SeedsMinimalNotePaymentFixture;
use Tests\TestCase;

final class NoteEditRefundSettlementDecisionPolicyTest extends TestCase
{
    use RefreshDatabase;
    use SeedsMinimalNotePaymentFixture;

    public function test_partial_refunded_note_edit_preloads_only_active_lines_and_preserves_refund_history(): void
    {
        $admin = $this->loginAsAuthorizedAdmin();
        $this->seedPartiallyRefundablePaidServiceNote();

        $this->actingAs($admin)
            ->from(route('admin.notes.show', ['noteId' => 'note-0041-a']))
            ->post(route('admin.notes.refunds.store', ['noteId' => 'note-0041-a']), [
                'selected_row_ids' => ['wi-0041-refund-a', 'wi-0041-refund-b'],
                'refunded_at' => '2026-06-30',
                'reason' => 'ADR-0041 partial refund before edit.',
            ])
            ->assertRedirect(route('admin.notes.index'))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('work_items', [
            'id' => 'wi-0041-refund-a',
            'note_id' => 'note-0041-a',
            'status' => WorkItem::STATUS_CANCELED,
        ]);
        $this->assertDatabaseHas('work_items', [
            'id' => 'wi-0041-refund-b',
            'note_id' => 'note-0041-a',
            'status' => WorkItem::STATUS_CANCELED,
        ]);
        $this->assertDatabaseHas('work_items', [
            'id' => 'wi-0041-active',
            'note_id' => 'note-0041-a',
            'status' => WorkItem::STATUS_OPEN,
        ]);
        self::assertSame(30000, (int) DB::table('notes')->where('id', 'note-0041-a')->value('total_rupiah'));
        self::assertSame(30000, (int) DB::table('customer_refunds')->where('note_id', 'note-0041-a')->sum('amount_rupiah'));
        self::assertSame(2, DB::table('refund_component_allocations')->where('note_id', 'note-0041-a')->count());

        $editResponse = $this->actingAs($admin)
            ->get(route('admin.notes.workspace.edit', ['noteId' => 'note-0041-a']));

        $editResponse->assertOk();

        $oldItems = $this->extractWorkspaceOldItems($editResponse->getContent());
        self::assertCount(1, $oldItems, 'Edit workspace must preload only active/current lines after partial refund.');
        self::assertSame('Active Service C', (string) ($oldItems[0]['service']['name'] ?? ''));

        $preEditDetail = app(NoteDetailPageDataBuilder::class)->build('note-0041-a');
        self::assertIsArray($preEditDetail);
        self::assertSame(3, count($preEditDetail['note']['rows']));
        self::assertTrue($this->detailRowsContain($preEditDetail['note']['rows'], 'Refunded Service A'));
        self::assertTrue($this->detailRowsContain($preEditDetail['note']['rows'], 'Refunded Service B'));
        self::assertTrue($this->detailRowsContain($preEditDetail['note']['rows'], 'Active Service C'));

        $this->actingAs($admin)
            ->patch(route('admin.notes.workspace.update', ['noteId' => 'note-0041-a']), [
                'note' => [
                    'customer_name' => 'ADR 0041 Customer Revised',
                    'customer_phone' => '08123456789',
                    'transaction_date' => '2026-06-30',
                ],
                'reason' => 'ADR-0041 edit remaining active line after partial refund.',
                'items' => [
                    [
                        'entry_mode' => 'service',
                        'description' => null,
                        'part_source' => 'none',
                        'service' => [
                            'name' => 'Active Service C Revised',
                            'price_rupiah' => '35000',
                            'notes' => null,
                        ],
                        'product_lines' => [],
                        'external_purchase_lines' => [],
                    ],
                ],
                'inline_payment' => [
                    'decision' => 'skip',
                    'payment_method' => null,
                    'paid_at' => null,
                    'amount_paid_rupiah' => null,
                    'amount_received_rupiah' => null,
                ],
            ])
            ->assertRedirect(route('admin.notes.show', ['noteId' => 'note-0041-a']))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('refund_component_allocations', [
            'note_id' => 'note-0041-a',
            'work_item_id' => 'wi-0041-refund-a',
            'refunded_amount_rupiah' => 10000,
        ]);
        $this->assertDatabaseHas('refund_component_allocations', [
            'note_id' => 'note-0041-a',
            'work_item_id' => 'wi-0041-refund-b',
            'refunded_amount_rupiah' => 20000,
        ]);
        self::assertSame(2, DB::table('refund_component_allocations')->where('note_id', 'note-0041-a')->count());
        self::assertSame(30000, (int) DB::table('customer_refunds')->where('note_id', 'note-0041-a')->sum('amount_rupiah'));

        $postEditDetail = app(NoteDetailPageDataBuilder::class)->build('note-0041-a');
        self::assertIsArray($postEditDetail);
        self::assertTrue($this->detailRowsContain($postEditDetail['note']['rows'], 'Active Service C Revised'));
        self::assertTrue(
            $this->revisionTimelineContains($postEditDetail['note']['revision_timeline']['timeline'] ?? [], 'Refunded Service A'),
            'Historical refunded line A must remain visible through revision history after the active edit.'
        );
        self::assertTrue(
            $this->revisionTimelineContains($postEditDetail['note']['revision_timeline']['timeline'] ?? [], 'Refunded Service B'),
            'Historical refunded line B must remain visible through revision history after the active edit.'
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function extractWorkspaceOldItems(string $html): array
    {
        self::assertMatchesRegularExpression(
            '/<script id="cashier-note-workspace-config" type="application\\/json">(.*?)<\\/script>/s',
            $html,
        );

        preg_match(
            '/<script id="cashier-note-workspace-config" type="application\\/json">(.*?)<\\/script>/s',
            $html,
            $matches,
        );

        $config = json_decode($matches[1] ?? '{}', true, flags: JSON_THROW_ON_ERROR);

        return is_array($config['oldItems'] ?? null) ? array_values($config['oldItems']) : [];
    }

    /**
     * @param list<array<string, mixed>> $rows
     */
    private function detailRowsContain(array $rows, string $label): bool
    {
        foreach ($rows as $row) {
            if (str_contains((string) ($row['line_label'] ?? ''), $label)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param list<array<string, mixed>> $entries
     */
    private function revisionTimelineContains(array $entries, string $label): bool
    {
        foreach ($entries as $entry) {
            $lines = is_array($entry['line_snapshot_rows'] ?? null) ? $entry['line_snapshot_rows'] : [];

            foreach ($lines as $line) {
                if (str_contains((string) ($line['label'] ?? ''), $label)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function seedPartiallyRefundablePaidServiceNote(): void
    {
        $noteId = 'note-0041-a';
        $date = '2026-06-30';

        $this->seedNoteBase($noteId, 'ADR 0041 Customer', $date, 60000, 'closed');

        $this->seedWorkItemBase('wi-0041-refund-a', $noteId, 1, WorkItem::TYPE_SERVICE_ONLY, WorkItem::STATUS_OPEN, 10000);
        $this->seedServiceDetailBase('wi-0041-refund-a', 'Refunded Service A', 10000, ServiceDetail::PART_SOURCE_NONE);

        $this->seedWorkItemBase('wi-0041-refund-b', $noteId, 2, WorkItem::TYPE_SERVICE_ONLY, WorkItem::STATUS_OPEN, 20000);
        $this->seedServiceDetailBase('wi-0041-refund-b', 'Refunded Service B', 20000, ServiceDetail::PART_SOURCE_NONE);

        $this->seedWorkItemBase('wi-0041-active', $noteId, 3, WorkItem::TYPE_SERVICE_ONLY, WorkItem::STATUS_OPEN, 30000);
        $this->seedServiceDetailBase('wi-0041-active', 'Active Service C', 30000, ServiceDetail::PART_SOURCE_NONE);

        $this->seedCurrentRevision(
            $noteId,
            $noteId . '-r001',
            'ADR 0041 Customer',
            null,
            $date,
            60000,
            [
                $this->serviceRevisionLine($noteId . '-r001-line-01', 'wi-0041-refund-a', 1, 'Refunded Service A', 10000),
                $this->serviceRevisionLine($noteId . '-r001-line-02', 'wi-0041-refund-b', 2, 'Refunded Service B', 20000),
                $this->serviceRevisionLine($noteId . '-r001-line-03', 'wi-0041-active', 3, 'Active Service C', 30000),
            ],
        );

        $this->seedCustomerPaymentBase('payment-0041-a', 60000, $date);
        DB::table('payment_component_allocations')->insert([
            [
                'id' => 'pca-0041-a-refund-a',
                'customer_payment_id' => 'payment-0041-a',
                'note_id' => $noteId,
                'work_item_id' => 'wi-0041-refund-a',
                'component_type' => PaymentComponentType::SERVICE_FEE,
                'component_ref_id' => 'wi-0041-refund-a',
                'component_amount_rupiah_snapshot' => 10000,
                'allocated_amount_rupiah' => 10000,
                'allocation_priority' => 1,
            ],
            [
                'id' => 'pca-0041-a-refund-b',
                'customer_payment_id' => 'payment-0041-a',
                'note_id' => $noteId,
                'work_item_id' => 'wi-0041-refund-b',
                'component_type' => PaymentComponentType::SERVICE_FEE,
                'component_ref_id' => 'wi-0041-refund-b',
                'component_amount_rupiah_snapshot' => 20000,
                'allocated_amount_rupiah' => 20000,
                'allocation_priority' => 2,
            ],
            [
                'id' => 'pca-0041-a-active',
                'customer_payment_id' => 'payment-0041-a',
                'note_id' => $noteId,
                'work_item_id' => 'wi-0041-active',
                'component_type' => PaymentComponentType::SERVICE_FEE,
                'component_ref_id' => 'wi-0041-active',
                'component_amount_rupiah_snapshot' => 30000,
                'allocated_amount_rupiah' => 30000,
                'allocation_priority' => 3,
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function serviceRevisionLine(
        string $id,
        string $workItemId,
        int $lineNo,
        string $serviceName,
        int $servicePriceRupiah,
    ): array {
        return [
            'id' => $id,
            'work_item_root_id' => $workItemId,
            'line_no' => $lineNo,
            'transaction_type' => WorkItem::TYPE_SERVICE_ONLY,
            'status' => WorkItem::STATUS_OPEN,
            'service_label' => $serviceName,
            'service_price_rupiah' => $servicePriceRupiah,
            'subtotal_rupiah' => $servicePriceRupiah,
            'payload' => [
                'work_item_root_id' => $workItemId,
                'transaction_type' => WorkItem::TYPE_SERVICE_ONLY,
                'status' => WorkItem::STATUS_OPEN,
                'external_purchase_lines' => [],
                'store_stock_lines' => [],
                'service' => [
                    'service_name' => $serviceName,
                    'service_price_rupiah' => $servicePriceRupiah,
                    'part_source' => ServiceDetail::PART_SOURCE_NONE,
                ],
            ],
        ];
    }
}
