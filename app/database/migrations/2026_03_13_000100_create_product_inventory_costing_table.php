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
        Schema::create('product_inventory_costing', function (Blueprint $table): void {
            $table->string('product_id')->primary();
            $table->integer('avg_cost_rupiah');
            $table->integer('inventory_value_rupiah');
        });

        $rows = DB::table('inventory_movements')
            ->selectRaw('product_id, SUM(qty_delta) as total_qty, SUM(total_cost_rupiah) as total_inventory_value_rupiah')
            ->where('movement_type', 'stock_in')
            ->groupBy('product_id')
            ->orderBy('product_id')
            ->get();

        $records = [];

        foreach ($rows as $row) {
            $totalQty = (int) $row->total_qty;
            $totalInventoryValueRupiah = (int) $row->total_inventory_value_rupiah;

            if ($totalQty <= 0) {
                continue;
            }

            $records[] = [
                'product_id' => (string) $row->product_id,
                'avg_cost_rupiah' => intdiv($totalInventoryValueRupiah, $totalQty),
                'inventory_value_rupiah' => $totalInventoryValueRupiah,
            ];
        }

        if ($records !== []) {
            DB::table('product_inventory_costing')->insert($records);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_inventory_costing');
    }
};
