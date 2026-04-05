<?php

declare(strict_types=1);

namespace Tests\Feature\Expense;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class CreateExpenseCategoryPageFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_accessing_create_expense_category_page(): void
    {
        $this->get(route('admin.expenses.categories.create'))
            ->assertRedirect(route('login'));
    }

    public function test_kasir_is_redirected_back_to_cashier_dashboard_when_accessing_create_expense_category_page(): void
    {
        $response = $this->actingAs($this->user('kasir'))
            ->get(route('admin.expenses.categories.create'));

        $response->assertRedirect(route('cashier.dashboard'));
        $response->assertSessionHas('error', 'Halaman admin hanya untuk role admin.');
    }

    public function test_admin_can_access_create_expense_category_page(): void
    {
        $response = $this->actingAs($this->user('admin'))
            ->get(route('admin.expenses.categories.create'));

        $response->assertOk();
        $response->assertSee('Tambah Kategori Pengeluaran');
        $response->assertSee('Kode');
        $response->assertSee('Nama');
        $response->assertSee('Deskripsi');
        $response->assertSee('Simpan Kategori');
    }

    private function user(string $role): User
    {
        $user = User::query()->create([
            'name' => 'Test',
            'email' => $role . '-expense-category-create@example.test',
            'password' => 'password123',
        ]);

        DB::table('actor_accesses')->insert([
            'actor_id' => (string) $user->getAuthIdentifier(),
            'role' => $role,
        ]);

        return $user;
    }
}
