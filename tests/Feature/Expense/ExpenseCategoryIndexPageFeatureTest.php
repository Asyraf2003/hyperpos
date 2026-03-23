<?php

declare(strict_types=1);

namespace Tests\Feature\Expense;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class ExpenseCategoryIndexPageFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_accessing_expense_category_index_page(): void
    {
        $this->get(route('admin.expenses.categories.index'))
            ->assertRedirect(route('login'));
    }

    public function test_kasir_is_redirected_back_to_cashier_dashboard_when_accessing_expense_category_index_page(): void
    {
        $response = $this->actingAs($this->user('kasir'))
            ->get(route('admin.expenses.categories.index'));

        $response->assertRedirect(route('cashier.dashboard'));
        $response->assertSessionHas('error', 'Halaman admin hanya untuk role admin.');
    }

    public function test_admin_can_access_expense_category_index_page(): void
    {
        $this->seedExpenseCategory('expense-category-1', 'EXP-ELEC', 'Listrik Bengkel', true, 'Token atau tagihan listrik');
        $this->seedExpenseCategory('expense-category-2', 'EXP-MISC', 'Lain-lain', false, 'Pengeluaran tak terduga');

        $response = $this->actingAs($this->user('admin'))
            ->get(route('admin.expenses.categories.index'));

        $response->assertOk();
        $response->assertSee('Master Kategori Pengeluaran');
        $response->assertSee('Tambah Kategori');
        $response->assertSee('EXP-ELEC');
        $response->assertSee('Listrik Bengkel');
        $response->assertSee('Aktif');
        $response->assertSee('EXP-MISC');
        $response->assertSee('Lain-lain');
        $response->assertSee('Nonaktif');
    }

    private function user(string $role): User
    {
        $user = User::query()->create([
            'name' => 'Test',
            'email' => $role . '-expense-category-index@example.test',
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
