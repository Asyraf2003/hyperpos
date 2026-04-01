<?php

declare(strict_types=1);

namespace Tests\Feature\Note;

use App\Core\Note\WorkItem\ServiceDetail;
use App\Core\Note\WorkItem\WorkItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class UpdateTransactionWorkspaceFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_can_update_unpaid_service_only_note_via_workspace(): void
    {
        $this->loginAsKasir();

        $user = User::query()->create([
            'name' => 'Kasir Update Workspace',
            'email' => 'update-workspace@example.test',
            'password' => 'password',
        ]);

        DB::table('actor_accesses')->insert([
            'actor_id' => (string) $user->getAuthIdentifier(),
            'role' => 'kasir',
        ]);

        DB::table('notes')->insert([
            'id' => 'note-update-1',
            'customer_name' => 'Budi Lama',
            'customer_phone' => '0811111111',
            'transaction_date' => '2026-04-03',
            'total_rupiah' => 50000,
        ]);

        DB::table('work_items')->insert([
            'id' => 'work-item-old-1',
            'note_id' => 'note-update-1',
            'line_no' => 1,
            'transaction_type' => WorkItem::TYPE_SERVICE_ONLY,
            'status' => WorkItem::STATUS_OPEN,
            'subtotal_rupiah' => 50000,
        ]);

        DB::table('work_item_service_details')->insert([
            'work_item_id' => 'work-item-old-1',
            'service_name' => 'Servis Lama',
            'service_price_rupiah' => 50000,
            'part_source' => ServiceDetail::PART_SOURCE_NONE,
        ]);

        $response = $this->actingAs($user)->patch(
            route('cashier.notes.workspace.update', ['noteId' => 'note-update-1']),
            [
                'note' => [
                    'customer_name' => 'Budi Baru',
                    'customer_phone' => '0822222222',
                    'transaction_date' => '2026-04-04',
                ],
                'items' => [
                    [
                        'entry_mode' => 'service',
                        'description' => '',
                        'part_source' => 'none',
                        'service' => [
                            'name' => 'Servis Baru',
                            'price_rupiah' => '70000',
                            'notes' => '',
                        ],
                        'product_lines' => [],
                        'external_purchase_lines' => [],
                    ],
                ],
            ],
        );

        $response->assertRedirect(route('cashier.notes.show', ['noteId' => 'note-update-1']));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('notes', [
            'id' => 'note-update-1',
            'customer_name' => 'Budi Baru',
            'customer_phone' => '0822222222',
            'transaction_date' => '2026-04-04',
            'total_rupiah' => 70000,
        ]);

        $this->assertDatabaseMissing('work_items', [
            'id' => 'work-item-old-1',
        ]);

        $this->assertDatabaseHas('work_items', [
            'note_id' => 'note-update-1',
            'line_no' => 1,
            'transaction_type' => WorkItem::TYPE_SERVICE_ONLY,
            'status' => WorkItem::STATUS_OPEN,
            'subtotal_rupiah' => 70000,
        ]);

        $newWorkItemId = (string) DB::table('work_items')
            ->where('note_id', 'note-update-1')
            ->value('id');

        $this->assertNotSame('', $newWorkItemId);

        $this->assertDatabaseHas('work_item_service_details', [
            'work_item_id' => $newWorkItemId,
            'service_name' => 'Servis Baru',
            'service_price_rupiah' => 70000,
            'part_source' => ServiceDetail::PART_SOURCE_NONE,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'transaction_workspace_updated',
        ]);
    }
}
