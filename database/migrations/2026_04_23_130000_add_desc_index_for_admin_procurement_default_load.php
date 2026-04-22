<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(
            'CREATE INDEX silp_voided_shipdesc_invoiceasc_idx
             ON supplier_invoice_list_projection (
                 voided_at,
                 shipment_date DESC,
                 supplier_invoice_id ASC
             )'
        );
    }

    public function down(): void
    {
        DB::statement(
            'DROP INDEX silp_voided_shipdesc_invoiceasc_idx
             ON supplier_invoice_list_projection'
        );
    }
};
