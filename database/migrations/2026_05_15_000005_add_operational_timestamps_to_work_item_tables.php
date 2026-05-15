<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLES = [
        'work_items',
        'work_item_service_details',
        'work_item_external_purchase_lines',
        'work_item_store_stock_lines',
    ];

    public function up(): void
    {
        foreach (self::TABLES as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
            });
        }

        $now = now();

        foreach (self::TABLES as $tableName) {
            DB::table($tableName)
                ->whereNull('created_at')
                ->update([
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
        }
    }

    public function down(): void
    {
        foreach (array_reverse(self::TABLES) as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->dropColumn(['created_at', 'updated_at']);
            });
        }
    }
};
