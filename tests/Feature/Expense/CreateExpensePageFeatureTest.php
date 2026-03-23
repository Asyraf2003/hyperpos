<?php

declare(strict_types=1);

namespace Tests\Feature\Expense;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class CreateExpensePageFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_accessing_create_expense_page(): void
    {
        $this->get(route('admin.expenses.create'))
            ->assertRedirect(route('login'));
    }

    public function test_kasir_is_redirected_back_to_cashier_dashboard_when_accessing_create_expense_page(): void
    {
        $response = $this->actingAs($this->user('kasir'))
            ->get(route('admin.expenses.create'));

        $response->assertRedirect(route('cashier.dashboard'));
        $response->assertSessionHas('error', 'Halaman admin hanya untuk role admin.');
    }

    public function test_admin_can_access_create_expense_page_with_active_category_options(): void
    {
        $this->seedExpenseCategory('expense-category-1', 'EXP-ELEC', 'Listrik Bengkel', true, 'Token atau tagihan listrik');
        $this->seedExpenseCategory('expense-category-2', 'EXP-MISC', 'Lain-lain', false, 'Pengeluaran tak terduga');

        $response = $this->actingAs($this->user('admin'))
            ->get(route('admin.expenses.create'));

        $response->assertOk();
        $response->assertSee('Catat Pengeluaran Operasional');
        $response->assertSee('Kategori');
        $response->assertSee('Tanggal');
        $response->assertSee('Nominal');
        $response->assertSee('Metode Bayar');
        $response->assertSee('Status');
        $response->assertSee('Simpan Pengeluaran');

        $response->assertSee(route('admin.expenses.store'), false);
        $response->assertSee(now()->format('Y-m-d'), false);
        $response->assertSee('Listrik Bengkel (EXP-ELEC)');
        $response->assertDontSee('Lain-lain (EXP-MISC)');
        $response->assertDontSee('Referensi');
        $response->assertDontSee('Submit expense akan diaktifkan pada batch berikutnya.');
        $response->assertSee('admin-money-input.js');
    }

    private function user(string $role): User
    {
        $user = User::query()->create([
            'name' => 'Test',
            'email' => $role . '-expense-create@example.test',
            'password' => 'password123',
        ]);

        DB::table('actor_accesses')->insert([
            'actor_id' => (string) $user->getAuthIdentifier(),
            'role' => $role,
        ]);

        return $user;
    }

    private function seedExpenseCategory(
        string $id,
        string $code,
        string $name,
        bool $isActive,
        ?string $description = null,
    ): void {
        DB::table('expense_categories')->insert([
            'id' => $id,
            'code' => $code,
            'name' => $name,
            'description' => $description,
            'is_active' => $isActive,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
