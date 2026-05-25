<?php

declare(strict_types=1);

namespace Tests\Feature\Note;

use App\Adapters\Out\Persistence\Eloquent\IdentityAccess\EloquentUser as User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class CreateTransactionWorkspaceDuplicateSubmitFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_duplicate_create_workspace_submit_currently_creates_duplicate_notes_without_idempotency_guard(): void
    {
        $this->loginAsKasir();

        $user = User::query()->create([
            'name' => 'Kasir Duplicate Submit',
            'email' => 'create-duplicate-submit@example.test',
            'password' => 'password',
        ]);

        DB::table('actor_accesses')->insert([
            'actor_id' => (string) $user->getAuthIdentifier(),
            'role' => 'kasir',
        ]);

        $payload = [
            'note' => [
                'customer_name' => 'Duplicate Submit Customer',
                'customer_phone' => '081234567898',
                'transaction_date' => '2026-05-24',
            ],
            'items' => [[
                'entry_mode' => 'service',
                'part_source' => 'none',
                'pricing_mode' => 'manual_split',
                'package_total_rupiah' => null,
                'service' => [
                    'name' => 'Servis Duplicate Submit',
                    'price_rupiah' => 85000,
                    'notes' => '',
                ],
                'product_lines' => [[
                    'product_id' => '',
                    'qty' => '',
                    'unit_price_rupiah' => '',
                ]],
                'external_purchase_lines' => [[
                    'label' => '',
                    'qty' => '',
                    'unit_cost_rupiah' => '',
                ]],
            ]],
            'inline_payment' => [
                'decision' => 'pay_full',
                'payment_method' => 'cash',
                'paid_at' => '2026-05-24',
                'amount_received_rupiah' => 100000,
            ],
        ];

        $firstResponse = $this->actingAs($user)->post(route('notes.workspace.store'), $payload);
        $secondResponse = $this->actingAs($user)->post(route('notes.workspace.store'), $payload);

        $firstResponse->assertRedirect(route('cashier.notes.index'));
        $secondResponse->assertRedirect(route('cashier.notes.index'));

        $this->assertSame(
            2,
            DB::table('notes')->where('customer_name', 'Duplicate Submit Customer')->count(),
            'Current create workspace behavior creates duplicate notes for duplicate submits.'
        );

        $this->assertSame(2, DB::table('work_items')->count());
        $this->assertSame(2, DB::table('work_item_service_details')->count());
        $this->assertSame(2, DB::table('customer_payments')->count());
        $this->assertSame(2, DB::table('customer_payment_cash_details')->count());
        $this->assertSame(2, DB::table('payment_component_allocations')->count());
        $this->assertSame(2, DB::table('note_history_projection')->count());
    }
}
