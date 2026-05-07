<?php

declare(strict_types=1);

namespace Tests\Feature\Note;

use App\Core\Shared\Exceptions\DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\SeedsMinimalNotePaymentFixture;
use Tests\TestCase;

final class NoteReplacementOverpaidAllocationReplayFeatureTest extends TestCase
{
    use RefreshDatabase;
    use SeedsMinimalNotePaymentFixture;

    public function test_downward_replacement_rejects_overpaid_replay_and_rolls_back_original_allocation(): void
    {
        $user = $this->loginAsAuthorizedAdmin();
        $oldDate = date('Y-m-d', strtotime('-4 days'));
        $today = date('Y-m-d');

        $this->seedPaidProductOnlyNote($oldDate);

        $this->withoutExceptionHandling();

        $response = null;
        $thrown = null;

        try {
            $response = $this->actingAs($user)->patch(
                route('admin.notes.workspace.update', ['noteId' => 'note-1']),
                [
                    'note' => [
                        'customer_name' => 'Budi Rejected Downward Revision',
                        'customer_phone' => '08123456789',
                        'transaction_date' => $today,
                    ],
                    'items' => [
                        [
                            'entry_mode' => 'product',
                            'description' => null,
                            'part_source' => 'store_stock',
                            'service' => [
                                'name' => null,
                                'price_rupiah' => null,
                                'notes' => null,
                            ],
                            'product_lines' => [
                                [
                                    'product_id' => 'product-1',
                                    'qty' => 2,
                                    'unit_price_rupiah' => 100000,
                                    'price_basis' => 'revision_snapshot',
                                ],
                            ],
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
                ],
            );
        } catch (DomainException $exception) {
            $thrown = $exception;
        }

        if ($thrown === null) {
            $this->assertNotNull($response);
        } else {
            $this->assertSame(
                'Payment tidak bisa dialokasikan penuh ke komponen note.',
                $thrown->getMessage(),
            );
        }

        $this->assertDatabaseHas('notes', [
            'id' => 'note-1',
            'customer_name' => 'Budi',
            'total_rupiah' => 300000,
            'latest_revision_number' => 1,
        ]);

        $this->assertDatabaseHas('work_items', [
            'id' => 'wi-old-1',
            'note_id' => 'note-1',
            'subtotal_rupiah' => 300000,
        ]);

        $this->assertSame(
            300000,
            (int) DB::table('payment_component_allocations')
                ->where('note_id', 'note-1')
                ->where('customer_payment_id', 'payment-1')
                ->sum('allocated_amount_rupiah'),
        );

        $this->assertDatabaseHas('customer_payments', [
            'id' => 'payment-1',
            'amount_rupiah' => 300000,
        ]);

        $this->assertDatabaseMissing('note_revisions', [
            'note_root_id' => 'note-1',
            'revision_number' => 2,
        ]);

        $this->assertDatabaseMissing('payment_component_allocations', [
            'note_id' => 'note-1',
            'customer_payment_id' => 'payment-1',
            'allocated_amount_rupiah' => 200000,
        ]);

        if ($thrown === null) {
            $response->assertSessionHasErrors();
        }
    }
}
