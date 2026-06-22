<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supplier_invoice_lines', function (Blueprint $table): void {
            if (! Schema::hasColumn('supplier_invoice_lines', 'rounding_residue_rupiah')) {
                $table->integer('rounding_residue_rupiah')->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::table('supplier_invoice_lines', function (Blueprint $table): void {
            if (Schema::hasColumn('supplier_invoice_lines', 'rounding_residue_rupiah')) {
                $table->dropColumn('rounding_residue_rupiah');
            }
        });
    }
};
