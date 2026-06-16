<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_item_store_stock_lines', function (Blueprint $table): void {
            if (! Schema::hasColumn('work_item_store_stock_lines', 'base_total_rupiah')) {
                $table->integer('base_total_rupiah')->default(0)->after('qty');
            }

            if (! Schema::hasColumn('work_item_store_stock_lines', 'tax_input')) {
                $table->string('tax_input', 32)->nullable()->after('base_total_rupiah');
            }

            if (! Schema::hasColumn('work_item_store_stock_lines', 'tax_mode')) {
                $table->string('tax_mode', 16)->default('none')->after('tax_input');
            }

            if (! Schema::hasColumn('work_item_store_stock_lines', 'tax_rate_basis_points')) {
                $table->integer('tax_rate_basis_points')->nullable()->after('tax_mode');
            }

            if (! Schema::hasColumn('work_item_store_stock_lines', 'tax_amount_rupiah')) {
                $table->integer('tax_amount_rupiah')->default(0)->after('tax_rate_basis_points');
            }
        });

        DB::table('work_item_store_stock_lines')
            ->where('base_total_rupiah', 0)
            ->update([
                'base_total_rupiah' => DB::raw('line_total_rupiah'),
                'tax_mode' => 'none',
                'tax_amount_rupiah' => 0,
            ]);
    }

    public function down(): void
    {
        Schema::table('work_item_store_stock_lines', function (Blueprint $table): void {
            foreach ([
                'tax_amount_rupiah',
                'tax_rate_basis_points',
                'tax_mode',
                'tax_input',
                'base_total_rupiah',
            ] as $column) {
                if (Schema::hasColumn('work_item_store_stock_lines', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
