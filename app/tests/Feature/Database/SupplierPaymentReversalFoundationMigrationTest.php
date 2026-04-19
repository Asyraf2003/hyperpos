<?php

declare(strict_types=1);

namespace Tests\Feature\Database;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class SupplierPaymentReversalFoundationMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_supplier_payment_reversals_table_exists_with_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('supplier_payment_reversals'));

        $this->assertTrue(Schema::hasColumns('supplier_payment_reversals', [
            'id',
            'supplier_payment_id',
            'reason',
            'performed_by_actor_id',
            'created_at',
            'updated_at',
        ]));
    }

    public function test_supplier_payment_reversals_has_expected_unique_and_foreign_constraints(): void
    {
        $database = DB::getDatabaseName();

        $constraints = collect(DB::select(
            'SELECT CONSTRAINT_NAME, CONSTRAINT_TYPE
             FROM information_schema.TABLE_CONSTRAINTS
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?',
            [$database, 'supplier_payment_reversals']
        ));

        $this->assertTrue(
            $constraints->contains(
                fn (object $row): bool =>
                    $row->CONSTRAINT_NAME === 'uq_spr_payment'
                    && $row->CONSTRAINT_TYPE === 'UNIQUE'
            )
        );

        $this->assertTrue(
            $constraints->contains(
                fn (object $row): bool =>
                    $row->CONSTRAINT_NAME === 'fk_spr_payment'
                    && $row->CONSTRAINT_TYPE === 'FOREIGN KEY'
            )
        );
    }
}
