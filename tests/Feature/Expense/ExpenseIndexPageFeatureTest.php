<?php

declare(strict_types=1);

namespace Tests\Feature\Expense;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class ExpenseIndexPageFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_accessing_expense_index_page(): void
    {
        $this->get(route('admin.expenses.index'))
            ->assertRedirect(route('login'));
    }

    public function test_kasir_is_redirected_back_to_cashier_dashboard_when_accessing_expense_index_page(): void
    {
        $response = $this->actingAs($this->user('kasir'))
            ->get(route('admin.expenses.index'));

        $response->assertRedirect(route('cashier.dashboard'));
        $response->assertSessionHas('error', 'Halaman admin hanya untuk role admin.');
    }

    public function test_admin_can_access_expense_index_page(): void
    {
        $this->seedExpenseCategory('expense-category-1', 'EXP-ELEC-NEW', 'Listrik Bengkel Baru', true, 'Kategori terbaru');
        $this->seedOperationalExpense(
            'expense-1',
            'expense-category-1',
            'EXP-ELEC',
            'Listrik Bengkel',
            250000,
            '2026-03-23',
            'Bayar token listrik workshop',
            'cash',
            'INV-EXP-001',
            'posted',
        );

        $response = $this->actingAs($this->user('admin'))
            ->get(route('admin.expenses.index'));

        $response->assertOk();
        $response->assertSee('Daftar Pengeluaran Operasional');
        $response->assertSee('Catat Pengeluaran');
        $response->assertSee('Kelola Kategori');
        $response->assertSee('Listrik Bengkel');
        $response->assertSee('(EXP-ELEC)');
        $response->assertDontSee('Listrik Bengkel Baru');
        $response->assertDontSee('EXP-ELEC-NEW');
        $response->assertSee('Bayar token listrik workshop');
        $response->assertSee('cash');
        $response->assertSee('Posted');
    }

    private function user(string $role): User
    {
        $user = User::query()->create([
            'name' => 'Test',
            'email' => $role . '-expense-index@example.test',
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

    private function seedOperationalExpense(
        string $id,
        string $categoryId,
        string $categoryCodeSnapshot,
        string $categoryNameSnapshot,
        int $amountRupiah,
        string $expenseDate,
        string $description,
        string $paymentMethod,
        ?string $referenceNo,
        string $status,
    ): void {
        DB::table('operational_expenses')->insert([
            'id' => $id,
            'category_id' => $categoryId,
            'category_code_snapshot' => $categoryCodeSnapshot,
            'category_name_snapshot' => $categoryNameSnapshot,
            'amount_rupiah' => $amountRupiah,
            'expense_date' => $expenseDate,
            'description' => $description,
            'payment_method' => $paymentMethod,
            'reference_no' => $referenceNo,
            'status' => $status,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
