<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @var list<string>
     */
    private array $tables = [
        'supplier_invoices',
        'supplier_receipts',
        'supplier_payments',
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
            });

            $now = now();

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
        foreach (array_reverse($this->tables) as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->dropColumn(['created_at', 'updated_at']);
            });
        }
    }
};
