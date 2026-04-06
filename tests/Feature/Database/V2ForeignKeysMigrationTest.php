<?php

declare(strict_types=1);

namespace Tests\Feature\Database;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class V2ForeignKeysMigrationTest extends TestCase
{
    public function test_procurement_inventory_foreign_keys_exist(): void
    {
        $this->skipUnlessMysqlOrMariaDb();

        $this->assertForeignKey('supplier_invoices', 'supplier_id', 'fk_si_supplier', 'suppliers', 'id');
        $this->assertForeignKey('supplier_invoice_lines', 'supplier_invoice_id', 'fk_sil_invoice', 'supplier_invoices', 'id');
        $this->assertForeignKey('supplier_invoice_lines', 'product_id', 'fk_sil_product', 'products', 'id');
        $this->assertForeignKey('supplier_receipts', 'supplier_invoice_id', 'fk_sr_invoice', 'supplier_invoices', 'id');
        $this->assertForeignKey('supplier_receipt_lines', 'supplier_receipt_id', 'fk_srl_receipt', 'supplier_receipts', 'id');
        $this->assertForeignKey('supplier_receipt_lines', 'supplier_invoice_line_id', 'fk_srl_invoice_line', 'supplier_invoice_lines', 'id');
        $this->assertForeignKey('inventory_movements', 'product_id', 'fk_im_product', 'products', 'id');
        $this->assertForeignKey('product_inventory', 'product_id', 'fk_pi_product', 'products', 'id');
        $this->assertForeignKey('product_inventory_costing', 'product_id', 'fk_pic_product', 'products', 'id');
        $this->assertForeignKey('supplier_payments', 'supplier_invoice_id', 'fk_sp_invoice', 'supplier_invoices', 'id');
        $this->assertForeignKey('supplier_payment_proof_attachments', 'supplier_payment_id', 'fk_sppa_payment', 'supplier_payments', 'id');
    }

    public function test_transaction_finance_foreign_keys_exist(): void
    {
        $this->skipUnlessMysqlOrMariaDb();

        $this->assertForeignKey('work_items', 'note_id', 'fk_wi_note', 'notes', 'id');
        $this->assertForeignKey('work_item_service_details', 'work_item_id', 'fk_wisd_work_item', 'work_items', 'id');
        $this->assertForeignKey('work_item_external_purchase_lines', 'work_item_id', 'fk_wiepl_work_item', 'work_items', 'id');
        $this->assertForeignKey('work_item_store_stock_lines', 'work_item_id', 'fk_wissl_work_item', 'work_items', 'id');
        $this->assertForeignKey('work_item_store_stock_lines', 'product_id', 'fk_wissl_product', 'products', 'id');
        $this->assertForeignKey('payment_allocations', 'customer_payment_id', 'fk_pa_payment', 'customer_payments', 'id');
        $this->assertForeignKey('payment_allocations', 'note_id', 'fk_pa_note', 'notes', 'id');
        $this->assertForeignKey('customer_refunds', 'customer_payment_id', 'fk_cr_payment', 'customer_payments', 'id');
        $this->assertForeignKey('customer_refunds', 'note_id', 'fk_cr_note', 'notes', 'id');
        $this->assertForeignKey('payment_component_allocations', 'customer_payment_id', 'fk_pca_payment', 'customer_payments', 'id');
        $this->assertForeignKey('payment_component_allocations', 'note_id', 'fk_pca_note', 'notes', 'id');
        $this->assertForeignKey('payment_component_allocations', 'work_item_id', 'fk_pca_work_item', 'work_items', 'id');
        $this->assertForeignKey('refund_component_allocations', 'customer_refund_id', 'fk_rca_refund', 'customer_refunds', 'id');
        $this->assertForeignKey('refund_component_allocations', 'customer_payment_id', 'fk_rca_payment', 'customer_payments', 'id');
        $this->assertForeignKey('refund_component_allocations', 'note_id', 'fk_rca_note', 'notes', 'id');
        $this->assertForeignKey('refund_component_allocations', 'work_item_id', 'fk_rca_work_item', 'work_items', 'id');
    }

    public function test_note_mutation_and_workspace_foreign_keys_exist(): void
    {
        $this->skipUnlessMysqlOrMariaDb();

        $this->assertForeignKey('note_mutation_events', 'note_id', 'fk_nme_note', 'notes', 'id');
        $this->assertForeignKey('note_mutation_events', 'related_customer_payment_id', 'fk_nme_rel_payment', 'customer_payments', 'id');
        $this->assertForeignKey('note_mutation_events', 'related_customer_refund_id', 'fk_nme_rel_refund', 'customer_refunds', 'id');
        $this->assertForeignKey('note_mutation_snapshots', 'note_mutation_event_id', 'fk_nms_event', 'note_mutation_events', 'id');
        $this->assertForeignKey('transaction_workspace_drafts', 'note_id', 'fk_twd_note', 'notes', 'id');
    }

    private function skipUnlessMysqlOrMariaDb(): void
    {
        $driver = DB::getDriverName();

        if (! in_array($driver, ['mysql', 'mariadb'], true)) {
            $this->markTestSkipped('MySQL/MariaDB metadata assertions only.');
        }
    }

    private function assertForeignKey(
        string $table,
        string $column,
        string $constraintName,
        string $referencedTable,
        string $referencedColumn
    ): void {
        $databaseName = (string) DB::connection()->getDatabaseName();

        $row = DB::selectOne(
            'SELECT
                k.CONSTRAINT_NAME,
                k.TABLE_NAME,
                k.COLUMN_NAME,
                k.REFERENCED_TABLE_NAME,
                k.REFERENCED_COLUMN_NAME,
                r.DELETE_RULE
             FROM information_schema.KEY_COLUMN_USAGE k
             LEFT JOIN information_schema.REFERENTIAL_CONSTRAINTS r
               ON r.CONSTRAINT_SCHEMA = k.CONSTRAINT_SCHEMA
              AND r.TABLE_NAME = k.TABLE_NAME
              AND r.CONSTRAINT_NAME = k.CONSTRAINT_NAME
             WHERE k.TABLE_SCHEMA = ?
               AND k.TABLE_NAME = ?
               AND k.COLUMN_NAME = ?
               AND k.CONSTRAINT_NAME = ?
               AND k.REFERENCED_TABLE_NAME = ?
               AND k.REFERENCED_COLUMN_NAME = ?
             LIMIT 1',
            [
                $databaseName,
                $table,
                $column,
                $constraintName,
                $referencedTable,
                $referencedColumn,
            ]
        );

        self::assertNotNull(
            $row,
            "Foreign key {$constraintName} not found on {$table}.{$column} -> {$referencedTable}.{$referencedColumn}."
        );

        self::assertContains(
            (string) $row->DELETE_RULE,
            ['RESTRICT', 'NO ACTION'],
            "Unexpected delete rule for {$constraintName}."
        );
    }
}
