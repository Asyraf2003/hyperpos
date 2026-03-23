<?php

declare(strict_types=1);

namespace Tests\Feature\Expense;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class StoreExpenseCategoryHttpFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_store_expense_category_from_create_page(): void
    {
        $response = $this->actingAs($this->user('admin'))
            ->post(route('admin.expenses.categories.store'), [
                'code' => 'EXP-WIFI',
                'name' => 'Wifi',
                'description' => 'Tagihan internet bengkel',
            ]);

        $response->assertRedirect(route('admin.expenses.categories.index'));
        $response->assertSessionHas('success', 'Kategori pengeluaran berhasil dibuat.');

        $this->assertDatabaseHas('expense_categories', [
            'code' => 'EXP-WIFI',
            'name' => 'Wifi',
            'description' => 'Tagihan internet bengkel',
            'is_active' => 1,
        ]);
    }

    public function test_admin_store_expense_category_returns_back_with_error_when_duplicate_code_conflict_happens(): void
    {
        $this->seedExpenseCategory(
            'expense-category-1',
            'EXP-WIFI',
            'Wifi Lama',
            true,
            'Data lama',
        );

        $response = $this->from(route('admin.expenses.categories.create'))
            ->actingAs($this->user('admin'))
            ->post(route('admin.expenses.categories.store'), [
                'code' => 'EXP-WIFI',
                'name' => 'Wifi Baru',
                'description' => 'Tagihan internet baru',
            ]);

        $response->assertRedirect(route('admin.expenses.categories.create'));
        $response->assertSessionHasErrors([
            'expense_category' => 'Kode expense category sudah dipakai.',
        ]);

        $this->assertDatabaseCount('expense_categories', 1);
    }

    public function test_admin_store_expense_category_returns_back_with_validation_error_when_name_is_blank(): void
    {
        $response = $this->from(route('admin.expenses.categories.create'))
            ->actingAs($this->user('admin'))
            ->post(route('admin.expenses.categories.store'), [
                'code' => 'EXP-PARK',
                'name' => '',
                'description' => 'Biaya parkir',
            ]);

        $response->assertRedirect(route('admin.expenses.categories.create'));
        $response->assertSessionHasErrors(['name']);

        $this->assertDatabaseCount('expense_categories', 0);
    }

    private function user(string $role): User
    {
        $user = User::query()->create([
            'name' => 'Test',
            'email' => $role . '-expense-category-store@example.test',
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
