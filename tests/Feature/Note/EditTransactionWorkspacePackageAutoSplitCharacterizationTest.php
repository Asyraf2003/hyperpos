<?php

declare(strict_types=1);

namespace Tests\Feature\Note;

use App\Adapters\Out\Persistence\Eloquent\IdentityAccess\EloquentUser as User;
use App\Core\Note\WorkItem\WorkItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\SeedsMinimalNotePaymentFixture;
use Tests\TestCase;

final class EditTransactionWorkspacePackageAutoSplitCharacterizationTest extends TestCase
{
    use RefreshDatabase;
    use SeedsMinimalNotePaymentFixture;

    public function test_edit_workspace_preloads_service_store_stock_package_auto_split_multi_product_revision(): void
    {
        $this->seedOpenMultiProductPackageNote();

        $user = User::query()->create([
            'name' => 'Admin Package Revision Characterization',
            'email' => 'admin-package-revision-characterization@example.test',
            'password' => 'password',
        ]);

        DB::table('actor_accesses')->insert([
            'actor_id' => (string) $user->getAuthIdentifier(),
            'role' => 'admin',
        ]);

        $response = $this->actingAs($user)->get(
            route('admin.notes.workspace.edit', ['noteId' => 'note-edit-package-multi-001'])
        );

        $response->assertOk();

        $response->assertSee('Servis Paket Multi Original', false);
        $response->assertSee('product-package-edit-a', false);
        $response->assertSee('product-package-edit-b', false);
        $response->assertSee('250000', false);
    }

    private function seedOpenMultiProductPackageNote(): void
    {
        $this->seedNoteBase(
            'note-edit-package-multi-001',
            'Budi Edit Package Multi',
            '2026-05-31',
            250000,
            'open',
        );

        DB::table('notes')
            ->where('id', 'note-edit-package-multi-001')
            ->update([
                'operational_note' => 'Alasan awal package multi.',
            ]);

        $this->seedNotePaymentProduct(
            'product-package-edit-a',
            'PKG-EDIT-A',
            'Oli Edit Package A',
            'Federal',
            100,
            50000,
        );

        $this->seedNotePaymentProduct(
            'product-package-edit-b',
            'PKG-EDIT-B',
            'Busi Edit Package B',
            'NGK',
            100,
            30000,
        );

        DB::table('product_inventory')->insert([
            [
                'product_id' => 'product-package-edit-a',
                'qty_on_hand' => 10,
            ],
            [
                'product_id' => 'product-package-edit-b',
                'qty_on_hand' => 10,
            ],
        ]);

        DB::table('product_inventory_costing')->insert([
            [
                'product_id' => 'product-package-edit-a',
                'avg_cost_rupiah' => 35000,
                'inventory_value_rupiah' => 350000,
            ],
            [
                'product_id' => 'product-package-edit-b',
                'avg_cost_rupiah' => 20000,
                'inventory_value_rupiah' => 200000,
            ],
        ]);

        $this->seedWorkItemBase(
            'wi-edit-package-multi-001',
            'note-edit-package-multi-001',
            1,
            WorkItem::TYPE_SERVICE_WITH_STORE_STOCK_PART,
            WorkItem::STATUS_OPEN,
            250000,
        );

        $this->seedServiceDetailBase(
            'wi-edit-package-multi-001',
            'Servis Paket Multi Original',
            120000,
            'store_stock',
        );

        $this->seedStoreStockLineBase(
            'ssl-edit-package-multi-a',
            'wi-edit-package-multi-001',
            'product-package-edit-a',
            2,
            100000,
        );

        $this->seedStoreStockLineBase(
            'ssl-edit-package-multi-b',
            'wi-edit-package-multi-001',
            'product-package-edit-b',
            1,
            30000,
        );

        $this->seedServiceWithStoreStockCurrentRevision(
            'note-edit-package-multi-001',
            'note-edit-package-multi-001-r001',
            'wi-edit-package-multi-001',
            'Budi Edit Package Multi',
            '2026-05-31',
            250000,
            'Servis Paket Multi Original',
            120000,
            'ssl-edit-package-multi-a',
            'product-package-edit-a',
            2,
            100000,
        );

        $payload = DB::table('note_revision_lines')
            ->where('note_revision_id', 'note-edit-package-multi-001-r001')
            ->where('work_item_root_id', 'wi-edit-package-multi-001')
            ->value('payload');

        $decoded = json_decode((string) $payload, true, 512, JSON_THROW_ON_ERROR);

        $decoded['pricing_mode'] = 'package_auto_split';
        $decoded['package_total_rupiah'] = 250000;
        $decoded['parts_total_rupiah'] = 130000;
        $decoded['service_price_rupiah'] = 120000;

        $decoded['store_stock_lines'] = [
            [
                'id' => 'ssl-edit-package-multi-a',
                'work_item_id' => 'wi-edit-package-multi-001',
                'product_id' => 'product-package-edit-a',
                'qty' => 2,
                'line_total_rupiah' => 100000,
                'selling_price_rupiah' => 50000,
                'product_name_snapshot' => 'Oli Edit Package A',
            ],
            [
                'id' => 'ssl-edit-package-multi-b',
                'work_item_id' => 'wi-edit-package-multi-001',
                'product_id' => 'product-package-edit-b',
                'qty' => 1,
                'line_total_rupiah' => 30000,
                'selling_price_rupiah' => 30000,
                'product_name_snapshot' => 'Busi Edit Package B',
            ],
        ];

        DB::table('note_revision_lines')
            ->where('note_revision_id', 'note-edit-package-multi-001-r001')
            ->where('work_item_root_id', 'wi-edit-package-multi-001')
            ->update([
                'payload' => json_encode($decoded, JSON_THROW_ON_ERROR),
            ]);
    }
}
